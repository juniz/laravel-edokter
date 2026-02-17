<?php

namespace App\Http\Controllers\Domain\Billing;

use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Domain\Billing\Contracts\PaymentRepository;
use App\Http\Controllers\Controller;
use App\Models\Domain\Shared\Setting;
use App\Models\SettingApp;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private \App\Domain\Billing\Contracts\PaymentAdapterInterface $paymentAdapter,
        private PaymentRepository $paymentRepository
    ) {}

    public function index(Request $request): Response
    {
        // Check if this is admin route
        if ($request->routeIs('admin.invoices.index')) {
            $invoices = \App\Models\Domain\Billing\Invoice::with(['customer', 'order'])
                ->latest()
                ->paginate(15);

            return Inertia::render('admin/invoices/Index', [
                'invoices' => $invoices,
            ]);
        }

        // Customer route
        $customer = $request->user()->customer;
        
        if (! $customer) {
            return Inertia::render('invoices/Index', [
                'invoices' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'filters' => $request->only(['status', 'search', 'per_page']),
            ]);
        }

        // Build query dengan filter
        $query = \App\Models\Domain\Billing\Invoice::where('customer_id', $customer->id)
            ->with(['customer', 'payments']);

        // Filter by status
        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by invoice number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('number', 'like', "%{$search}%");
        }

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $invoices = $query->latest()->paginate($perPage);

        // Load pending payments untuk setiap invoice
        $invoices->getCollection()->transform(function ($invoice) {
            $pendingPayment = \App\Models\Domain\Billing\Payment::where('invoice_id', $invoice->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            $invoice->setAttribute('pending_payment_id', $pendingPayment?->id);

            return $invoice;
        });

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['status', 'search', 'per_page']),
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (! $invoice) {
            abort(404);
        }

        // Check if this is admin route
        if ($request->routeIs('admin.invoices.show')) {
            return Inertia::render('admin/invoices/Show', [
                'invoice' => $invoice->load(['items', 'payments', 'customer', 'order']),
            ]);
        }

        return Inertia::render('invoices/Show', [
            'invoice' => $invoice->load(['items', 'payments', 'customer']),
        ]);
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

    public function pay(Request $request, string $id)
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (! $invoice) {
            abort(404);
        }

        // Check ownership
        if ($request->user()->customer->id !== $invoice->customer_id) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice->id)
                ->with('info', 'Invoice ini sudah dibayar.');
        }

        // Cek apakah sudah ada payment pending untuk invoice ini
        $pendingPayment = \App\Models\Domain\Billing\Payment::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Jika sudah ada payment pending, langsung redirect ke halaman payment
        if ($pendingPayment) {
            return redirect()->route('customer.payments.show', $pendingPayment->id)
                ->with('info', 'Anda sudah memiliki pembayaran yang sedang menunggu. Silakan selesaikan pembayaran tersebut.');
        }

        if (! $request->has('payment_method') && ! $this->isManualOnly()) {
            return redirect()->back()->with('open_payment_modal', true)->with('invoice_id', $invoice->id);
        }

        $request->validate([
            'payment_method' => ['nullable', 'string'],
        ]);

        try {
            $paymentMethod = $request->input('payment_method');
            if (! $paymentMethod && $this->isManualOnly()) {
                $paymentMethod = 'manual';
            }

            $payment = $this->paymentAdapter->createCharge($invoice, [
                'payment_method' => $paymentMethod,
            ]);

            return redirect()->route('customer.payments.show', $payment->id)
                ->with('success', 'Silakan selesaikan pembayaran Anda.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Gagal memproses pembayaran: ' . $e->getMessage()]);
        }
    }

    public function checkPaymentStatus(Request $request, string $id)
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (! $invoice) {
            abort(404);
        }

        // Check ownership
        if ($request->user()->customer->id !== $invoice->customer_id) {
            abort(403);
        }

        // Cari last pending payment
        $payment = \App\Models\Domain\Billing\Payment::where('invoice_id', $invoice->id)
            ->latest()
            ->first();

        if (! $payment) {
            return redirect()->back()
                ->with('info', 'Belum ada data pembayaran untuk invoice ini.');
        }

        // Check status via adapter
        $updatedPayment = $this->paymentAdapter->checkStatus($payment);

        if ($updatedPayment && $updatedPayment->status === 'succeeded') {
             return redirect()->back()
                ->with('success', 'Pembayaran berhasil dikonfirmasi.');
        }

        return redirect()->back()
            ->with('info', 'Status pembayaran belum berubah. Silakan coba lagi nanti.');
    }

    public function markAsPaidManual(Request $request, string $id)
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (! $invoice) {
            abort(404);
        }

        if (! $request->user()->hasRole('admin')) {
            abort(403);
        }

        $existingSucceeded = \App\Models\Domain\Billing\Payment::where('invoice_id', $invoice->id)
            ->where('status', 'succeeded')
            ->exists();

        if ($existingSucceeded) {
            return redirect()->back()->with('info', 'Invoice ini sudah memiliki pembayaran berhasil.');
        }

        $payment = $this->paymentRepository->create([
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'provider_ref' => 'manual-'.now()->timestamp,
            'amount_cents' => $invoice->total_cents,
            'status' => 'pending',
            'raw_payload' => [
                'payment_method' => 'manual',
                'manual_marked_paid_by' => $request->user()->id,
            ],
        ]);

        $this->paymentRepository->markAsSucceeded($payment, $payment->raw_payload ?? []);

        return redirect()->back()->with('success', 'Invoice berhasil ditandai sebagai sudah dibayar.');
    }

    public function markAsUnpaidManual(Request $request, string $id)
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (! $invoice) {
            abort(404);
        }

        if (! $request->user()->hasRole('admin')) {
            abort(403);
        }

        $invoice->update(['status' => 'unpaid']);

        \App\Models\Domain\Billing\Payment::where('invoice_id', $invoice->id)
            ->whereIn('status', ['succeeded', 'pending'])
            ->update(['status' => 'failed']);

        return redirect()->back()->with('success', 'Invoice berhasil ditandai sebagai belum dibayar.');
    }

    public function download(Request $request, string $id)
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (! $invoice) {
            abort(404);
        }

        // Check ownership for customer route
        if ($request->routeIs('customer.invoices.download')) {
            if ($request->user()->customer->id !== $invoice->customer_id) {
                abort(403);
            }
        }

        // Load relationships
        $invoice->load(['items', 'payments', 'customer']);

        // Get company info from settings
        $setting = SettingApp::first();
        $companyName = $setting?->nama_app ?? 'Abahost';
        $companyAddress = null;
        $companyPhone = null;
        $companyEmail = null;
        $companyWebsite = null;
        $companyLogo = null;

        // Get logo path
        if ($setting && $setting->logo) {
            $logoPath = storage_path('app/public/' . $setting->logo);
            if (file_exists($logoPath)) {
                // Convert logo to base64 for PDF
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $companyLogo = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        // You can extend this to get more company info from settings or config
        if ($setting && isset($setting->seo)) {
            $seo = $setting->seo;
            if (is_string($seo)) {
                $seo = json_decode($seo, true) ?? [];
            }
            if (! is_array($seo)) {
                $seo = [];
            }
            $companyAddress = $seo['address'] ?? null;
            $companyPhone = $seo['phone'] ?? null;
            $companyEmail = $seo['email'] ?? null;
            $companyWebsite = $seo['website'] ?? null;
        }

        // Generate PDF
        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);

        $html = view('pdf.invoice', [
            'invoice' => $invoice,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
            'companyPhone' => $companyPhone,
            'companyEmail' => $companyEmail,
            'companyWebsite' => $companyWebsite,
            'companyLogo' => $companyLogo,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Invoice-' . $invoice->number . '.pdf';

        // Return PDF sebagai download stream
        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
