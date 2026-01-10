<?php

namespace App\Http\Controllers\Domain\Billing;

use App\Http\Controllers\Controller;
use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private \App\Domain\Billing\Contracts\PaymentAdapterInterface $paymentAdapter
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
        
        if (!$customer) {
            return Inertia::render('invoices/Index', [
                'invoices' => [],
            ]);
        }

        $invoices = $this->invoiceRepository->findByCustomer($customer->id);

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (!$invoice) {
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

    public function pay(Request $request, string $id)
    {
        $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $invoice = $this->invoiceRepository->findByUlid($id);

        if (!$invoice) {
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

        try {
            $payment = $this->paymentAdapter->createCharge($invoice, [
                'payment_method' => $request->payment_method,
            ]);

            return redirect()->route('customer.payments.show', $payment->id)
                ->with('success', 'Silakan selesaikan pembayaran Anda.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Gagal memproses pembayaran: ' . $e->getMessage()]);
        }
    }

    public function download(string $id)
    {
        $invoice = $this->invoiceRepository->findByUlid($id);

        if (!$invoice) {
            abort(404);
        }

        // TODO: Generate PDF invoice
        return response()->json(['message' => 'PDF generation not implemented yet']);
    }
}
