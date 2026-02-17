<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Application\Catalog\CheckoutCatalogService;
use App\Domain\Catalog\Contracts\ProductRepository;
use App\Http\Controllers\Controller;
use App\Models\Domain\Shared\Setting;
use App\Models\SettingApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        private ProductRepository $productRepository,
        private CheckoutCatalogService $checkoutCatalogService
    ) {}

    /**
     * Halaman catalog untuk guest (tidak perlu login)
     */
    public function guest(): Response
    {
        $products = collect($this->productRepository->findAllActive())
            ->sortBy('price_cents')
            ->values()
            ->all();

        // Load features untuk setiap product
        foreach ($products as $product) {
            $product->load('features');
        }

        // Get setting dari database
        $setting = SettingApp::first();
        $companyName = $setting?->nama_app ?? config('app.name', 'AbaHost');
        $companyLogo = $setting?->logo ? Storage::url($setting->logo) : null;

        return Inertia::render('catalog/Guest', [
            'products' => $products,
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
        ]);
    }

    public function index(): Response
    {
        // Jika user belum login, redirect ke halaman guest
        if (! Auth::check()) {
            return $this->guest();
        }

        $products = $this->productRepository->findAllActive();

        // Load features untuk setiap product
        foreach ($products as $product) {
            $product->load('features');
        }

        // Get setting untuk PPH rate dari database atau config
        $setting = \App\Models\Domain\Shared\Setting::where('key', 'billing_settings')->first();
        $pphRate = $setting?->value['pph_rate'] ?? config('billing.pph_rate', 0.11);

        return Inertia::render('catalog/Index', [
            'products' => $products,
            'pphRate' => $pphRate,
        ]);
    }

    public function show(string $slug): Response
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        $product->load('features');

        // Get tax rate
        $billingSetting = \App\Models\Domain\Shared\Setting::where('key', 'billing_settings')->first();
        $pphRate = $billingSetting?->value['pph_rate'] ?? config('billing.pph_rate', 0.11);

        return Inertia::render('catalog/Show', [
            'product' => $product,
            'pphRate' => (float) $pphRate,
        ]);
    }

    /**
     * Halaman detail produk untuk guest (tidak perlu login)
     */
    public function guestShow(string $slug): Response
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        $product->load('features');

        // Get setting dari database
        $setting = SettingApp::first();
        $companyName = $setting?->nama_app ?? config('app.name', 'AbaHost');
        $companyLogo = $setting?->logo ? Storage::url($setting->logo) : null;

        // Construct plans based on enabled durations
        $plans = [];

        if ($product->duration_1_month_enabled) {
            $plans[] = [
                'id' => 'monthly',
                'code' => 'Bulanan',
                'billing_cycle' => 'bulan',
                'price_cents' => $product->price_cents,
                'currency' => $product->currency,
                'setup_fee_cents' => $product->setup_fee_cents ?? 0,
                'trial_days' => $product->trial_days ?? 0,
            ];
        }

        if ($product->duration_12_months_enabled) {
            // Calculate annual price with discount
            $annualPriceWithoutDiscount = $product->price_cents * 12;
            $annualDiscountPercent = $product->annual_discount_percent ?? 0;
            $annualDiscountAmount = (int) round($annualPriceWithoutDiscount * ($annualDiscountPercent / 100));
            $annualPriceWithDiscount = $annualPriceWithoutDiscount - $annualDiscountAmount;

            $plans[] = [
                'id' => 'yearly',
                'code' => 'Tahunan',
                'billing_cycle' => 'tahun',
                'price_cents' => $annualPriceWithDiscount,
                'original_price_cents' => $annualPriceWithoutDiscount,
                'discount_percent' => $annualDiscountPercent,
                'discount_amount_cents' => $annualDiscountAmount,
                'currency' => $product->currency,
                'setup_fee_cents' => $product->setup_fee_cents ?? 0,
                'trial_days' => $product->trial_days ?? 0,
            ];
        }

        return Inertia::render('catalog/GuestShow', [
            'product' => $product,
            'plans' => $plans,
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
        ]);
    }

    /**
     * Halaman checkout untuk guest
     */
    public function guestCheckout(string $slug): Response
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404);
        }

        // Load features
        $product->load('features');

        // Get setting dari database
        $setting = SettingApp::first();
        $companyName = $setting?->nama_app ?? config('app.name', 'AbaHost');
        $companyLogo = $setting?->logo ? Storage::url($setting->logo) : null;

        // Get tax rate
        $billingSetting = \App\Models\Domain\Shared\Setting::where('key', 'billing_settings')->first();
        $pphRate = $billingSetting?->value['pph_rate'] ?? config('billing.pph_rate', 0.11);

        // Find active auto-apply promo
        // Calculate actual discount value for proper sorting (percent vs fixed)
        $promo = \App\Models\Domain\Catalog\Coupon::where('is_auto_apply', true)
            ->where(function ($query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->get()
            ->filter(function ($coupon) use ($product) {
                // Check product applicability
                if (empty($coupon->applicable_product_ids)) {
                    return true;
                }

                return in_array($product->id, $coupon->applicable_product_ids);
            })
            ->sortByDesc(function ($coupon) use ($product) {
                // Calculate actual discount amount for comparison
                // Use product price as basis for percentage calculation
                $productPriceCents = $product->price_cents ?? 0;

                if ($coupon->type === 'percent') {
                    // For percentage: discount = price * (value / 100)
                    return (int) round($productPriceCents * ($coupon->value / 100));
                }

                // For fixed: discount = value * 100 (convert from rupiah to cents)
                return (int) round($coupon->value * 100);
            })
            ->first();

        return Inertia::render('catalog/Checkout', [
            'product' => $product,
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
            'pphRate' => (float) $pphRate,
            'promo' => $promo,
        ]);
    }

    public function checkout(Request $request)
    {
        $rules = [
            'product_id' => ['required', 'string'],
            'duration_months' => ['required', 'integer', 'in:1,12'],
            'domains' => ['nullable', 'array'],
            'domains.*.domain' => ['required', 'string'],
            'domains.*.price_cents' => ['required', 'integer', 'min:0'],
            'domains.*.original_price_cents' => ['nullable', 'integer', 'min:0'],
            'domains.*.discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];

        if ($this->isManualOnly()) {
            $rules['payment_method'] = ['nullable', 'string'];
        } else {
            $rules['payment_method'] = ['required', 'string'];
        }

        $request->validate($rules);

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk melakukan pembayaran.');
        }

        $customer = $user->customer;

        if (! $customer) {
            return redirect()->back()
                ->withErrors(['error' => 'Customer profile tidak ditemukan. Silakan lengkapi profil Anda.']);
        }

        // Validasi durasi enabled untuk product
        $product = $this->productRepository->findByUlid($request->product_id);
        if (! $product) {
            return redirect()->back()
                ->withErrors(['error' => 'Product tidak ditemukan.']);
        }

        if ($request->duration_months === 1 && ! ($product->duration_1_month_enabled ?? true)) {
            return redirect()->back()
                ->withErrors(['error' => 'Durasi 1 bulan tidak tersedia untuk product ini.']);
        }

        if ($request->duration_months === 12 && ! ($product->duration_12_months_enabled ?? true)) {
            return redirect()->back()
                ->withErrors(['error' => 'Durasi 12 bulan tidak tersedia untuk product ini.']);
        }

        try {
            $paymentMethod = $request->input('payment_method');
            if (! $paymentMethod && $this->isManualOnly()) {
                $paymentMethod = 'manual';
            }

            $payment = $this->checkoutCatalogService->execute($customer, [
                'product_id' => $request->product_id,
                'payment_method' => $paymentMethod,
                'duration_months' => $request->duration_months,
                'domains' => $request->domains ?? [],
            ]);

            // Redirect ke halaman payment
            // Setelah redirect, halaman pembayaran akan otomatis refresh setiap 5 detik
            // untuk mengecek status pembayaran. Setelah webhook Midtrans mengirim notifikasi,
            // status akan terupdate secara real-time (maksimal 5 detik delay)
            return redirect()->route('customer.payments.show', $payment->id)
                ->with('success', 'Pembayaran berhasil dibuat. Silakan selesaikan pembayaran Anda.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    private function isManualOnly(): bool
    {
        $defaultGateway = config('payment.default', 'manual');

        try {
            $setting = Setting::where('key', 'payment_gateway_settings')->first();
            $value = $setting?->value ?? [];

            $defaultGateway = $value['default_gateway'] ?? $defaultGateway;
            $midtransEnabled = $value['midtrans_enabled'] ?? true;

            if ($defaultGateway === 'midtrans' && $midtransEnabled === false) {
                $defaultGateway = 'manual';
            }
        } catch (\Throwable) {
        }

        return $defaultGateway === 'manual';
    }
}
