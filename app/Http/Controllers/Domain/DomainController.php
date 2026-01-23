<?php

namespace App\Http\Controllers\Domain;

use App\Application\Domain\CheckoutDomainService;
use App\Application\Rdash\Domain\CheckDomainAvailabilityService;
use App\Application\Rdash\Domain\GetDomainDetailsService;
use App\Application\Rdash\Domain\ListDomainsService;
use App\Application\Rdash\Domain\RegisterDomainViaRdashService;
use App\Domain\Customer\Contracts\CustomerRepository;
use App\Http\Controllers\Controller;
use App\Models\Domain\Customer\Domain;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DomainController extends Controller
{
    public function __construct(
        private ListDomainsService $listDomainsService,
        private CheckDomainAvailabilityService $checkAvailabilityService,
        private GetDomainDetailsService $getDomainDetailsService,
        private RegisterDomainViaRdashService $registerDomainService,
        private CheckoutDomainService $checkoutDomainService,
        private CustomerRepository $customerRepository
    ) {}

    /**
     * Display a listing of domains
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'customer_id',
            'name',
            'status',
            'verification_status',
            'required_document',
            'created_range',
            'expired_range',
            'page',
            'limit',
        ]);

        // Get local domains
        $query = Domain::with('customer');

        // Jika customer (bukan admin), hanya tampilkan domains mereka sendiri
        if (auth()->user()->hasRole('customer') || auth()->user()->hasRole('user')) {
            $customer = auth()->user()->customer;
            if ($customer) {
                $query->where('customer_id', $customer->id);
            } else {
                // Jika user tidak punya customer, return empty
                $query->whereRaw('1 = 0');
            }
        } elseif ($request->has('customer_id') && $request->customer_id) {
            // Admin bisa filter by customer_id
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('name') && $request->name) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $domains = $query->latest()->paginate($request->get('per_page', 15));

        // Get domains from RDASH (hanya untuk admin atau jika customer_id di-filter)
        $rdashDomains = [];
        if (auth()->user()->hasRole('admin')) {
            $rdashDomains = $this->listDomainsService->execute($filters);
        }

        return Inertia::render('domains/Index', [
            'domains' => $domains,
            'rdashDomains' => array_map(fn($d) => $d->toArray(), $rdashDomains),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new domain
     */
    public function create()
    {
        // Jika customer, gunakan customer mereka sendiri
        if (auth()->user()->hasRole('customer') || auth()->user()->hasRole('user')) {
            $customer = auth()->user()->customer;
            if (! $customer || ! $customer->rdash_customer_id || $customer->rdash_sync_status !== 'synced') {
                return redirect()->route('customer.domains.index')->withErrors([
                    'error' => 'Customer belum di-sync ke RDASH. Silakan hubungi admin untuk sync customer terlebih dahulu.',
                ]);
            }
            $customers = collect([$customer]);
        } else {
            // Admin bisa pilih customer
            $customers = \App\Models\Domain\Customer\Customer::whereNotNull('rdash_customer_id')
                ->where('rdash_sync_status', 'synced')
                ->get();
        }

        // Get billing settings
        $setting = \App\Models\Domain\Shared\Setting::where('key', 'billing_settings')->first();
        $billingSettings = [
            'pph_rate' => $setting?->value['pph_rate'] ?? config('billing.pph_rate', 0.11),
            'application_fee' => $setting?->value['application_fee'] ?? 0,
        ];

        return Inertia::render('domains/Form', [
            'customers' => $customers,
            'billingSettings' => $billingSettings,
        ]);
    }

    /**
     * Store a newly created domain
     */
    public function store(Request $request)
    {
        // Jika customer, gunakan customer mereka sendiri
        $customerId = null;
        if (auth()->user()->hasRole('customer') || auth()->user()->hasRole('user')) {
            $customer = auth()->user()->customer;
            if (! $customer) {
                return redirect()->back()->withErrors(['error' => 'Customer tidak ditemukan.']);
            }
            $customerId = $customer->id;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period' => 'required|integer|min:1|max:10',
            'customer_id' => $customerId ? 'sometimes|string' : 'required|string|exists:customers,id',
            // Nameserver & opsi lain dibuat benar-benar opsional (sesuai Swagger)
            'nameserver' => 'nullable|array|max:5',
            'nameserver.*' => 'nullable|string|max:255',
            'buy_whois_protection' => 'nullable|boolean',
            'include_premium_domains' => 'nullable|boolean',
            'registrant_contact_id' => 'nullable|integer',
            'auto_renew' => 'nullable|boolean',
            'payment_method' => 'nullable|string|in:credit_card,bank_transfer,bca_va,bni_va,bri_va,mandiri_va,permata_va,cimb_va,danamon_va,bsi_va,qris,gopay,shopeepay,dana,ovo,linkaja,cstore,indomaret,alfamart,manual', // Payment method
        ]);

        // Jika payment_method bukan manual, gunakan checkout service dengan Midtrans
        // Check validated array to ensure payment_method is set and not null or manual
        if (isset($validated['payment_method']) && $validated['payment_method'] !== null && $validated['payment_method'] !== 'manual') {
            return $this->checkoutWithPayment($request, $validated, $customerId);
        }

        // Default: register langsung tanpa payment (untuk admin atau manual payment)
        // Format nameservers untuk API RDASH (hanya kirim yang terisi, mengikuti Swagger: opsional)
        $data = [
            'name' => $validated['name'],
            'period' => $validated['period'],
            'customer_id' => $customerId ?? $validated['customer_id'],
            'auto_renew' => $validated['auto_renew'] ?? false,
        ];

        if (isset($validated['nameserver'])) {
            $nameservers = array_values(array_filter(
                $validated['nameserver'],
                static fn($ns): bool => filled($ns)
            ));

            foreach ($nameservers as $index => $ns) {
                $data["nameserver[{$index}]"] = $ns;
            }
        }

        if (isset($validated['buy_whois_protection'])) {
            $data['buy_whois_protection'] = $validated['buy_whois_protection'];
        }

        if (isset($validated['include_premium_domains'])) {
            $data['include_premium_domains'] = $validated['include_premium_domains'];
        }

        if (isset($validated['registrant_contact_id'])) {
            $data['registrant_contact_id'] = $validated['registrant_contact_id'];
        }

        $result = $this->registerDomainService->execute($data);

        if (! $result['success']) {
            return redirect()->back()->withErrors(['error' => $result['message']]);
        }

        // Redirect berdasarkan role
        if (auth()->user()->hasRole('customer') || auth()->user()->hasRole('user')) {
            return redirect()->route('customer.domains.index')->with('success', $result['message']);
        }

        return redirect()->route('admin.domains.index')->with('success', $result['message']);
    }

    /**
     * Checkout domain dengan payment
     */
    private function checkoutWithPayment(Request $request, array $validated, ?string $customerId)
    {
        $data = [
            'name' => $validated['name'],
            'period' => $validated['period'],
            'customer_id' => $customerId ?? $validated['customer_id'],
            'auto_renew' => $validated['auto_renew'] ?? false,
            'payment_method' => $validated['payment_method'] ?? 'bca_va',
        ];

        if (isset($validated['nameserver'])) {
            $data['nameserver'] = $validated['nameserver'];
        }

        if (isset($validated['buy_whois_protection'])) {
            $data['buy_whois_protection'] = $validated['buy_whois_protection'];
        }

        if (isset($validated['include_premium_domains'])) {
            $data['include_premium_domains'] = $validated['include_premium_domains'];
        }

        if (isset($validated['registrant_contact_id'])) {
            $data['registrant_contact_id'] = $validated['registrant_contact_id'];
        }

        $result = $this->checkoutDomainService->execute($data);

        if (! $result['success']) {
            return redirect()->back()->withErrors(['error' => $result['message']]);
        }

        // Redirect ke invoice payment page dengan payment token
        $payment = $result['payment'];
        $redirectUrl = $payment->raw_payload['redirect_url'] ?? null;
        $snapToken = $payment->raw_payload['snap_token'] ?? null; // Assume snap_token might be here
        // If not in raw_payload, check if it's in core_api_response (some adapters structure it differently)
        if (!$snapToken && isset($payment->raw_payload['token'])) {
             $snapToken = $payment->raw_payload['token'];
        }

        // Return JSON for frontend handling (Snap Popup)
        $routePrefix = auth()->user()->hasRole('admin') ? 'admin' : 'customer';
        $invoiceRoute = $routePrefix . '.invoices.show';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'invoice_url' => route($invoiceRoute, $result['invoice']->id),
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
            ]);
        }

        $paymentMethod = $payment->raw_payload['payment_method'] ?? null;

        // Untuk payment method yang memiliki redirect_url (credit card, e-wallet dengan deeplink)
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        // Untuk payment method tanpa redirect_url (VA, convenience store)
        // Redirect ke invoice page dengan payment details
        return redirect()->route($invoiceRoute, $result['invoice']->id)
            ->with('success', $result['message'])
            ->with('payment_details', [
                'method' => $paymentMethod,
                'order_id' => $payment->raw_payload['order_id'] ?? null,
                'core_api_response' => $payment->raw_payload['core_api_response'] ?? null,
            ]);
    }

    /**
     * Display the specified domain
     */
    public function show(Domain $domain)
    {
        $domain->load('customer');

        // Check authorization: customer hanya bisa lihat domains mereka sendiri
        if (auth()->user()->hasRole('customer') || auth()->user()->hasRole('user')) {
            $customer = auth()->user()->customer;
            if (! $customer || $domain->customer_id !== $customer->id) {
                abort(403, 'Unauthorized access to this domain');
            }
        }

        // Get RDASH domain details if available
        $rdashDomain = null;
        if ($domain->rdash_domain_id) {
            $rdashDomain = $this->getDomainDetailsService->executeById($domain->rdash_domain_id);
        }

        return Inertia::render('domains/Show', [
            'domain' => $domain,
            'rdashDomain' => $rdashDomain?->toArray(),
        ]);
    }

    /**
     * Check domain availability
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string',
            'include_premium_domains' => 'sometimes|boolean',
        ]);

        $availability = $this->checkAvailabilityService->execute(
            $validated['domain'],
            $validated['include_premium_domains'] ?? false
        );

        return response()->json([
            'success' => true,
            'data' => $availability->toArray(),
        ]);
    }

    /**
     * Get domain details by name
     */
    public function getDetails(Request $request)
    {
        $validated = $request->validate([
            'domain_name' => 'required|string',
        ]);

        $domain = $this->getDomainDetailsService->execute($validated['domain_name']);

        if (! $domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $domain->toArray(),
        ]);
    }
}
