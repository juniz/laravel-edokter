<?php

namespace App\Http\Controllers\Domain\Billing;

use App\Http\Controllers\Controller;
use App\Domain\Billing\Contracts\InvoiceRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepository $invoiceRepository
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
