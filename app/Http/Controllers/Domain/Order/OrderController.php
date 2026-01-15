<?php

namespace App\Http\Controllers\Domain\Order;

use App\Application\Billing\GenerateInvoiceService;
use App\Application\Order\PlaceOrderService;
use App\Domain\Order\Contracts\OrderRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepository $orderRepository,
        private PlaceOrderService $placeOrderService,
        private GenerateInvoiceService $generateInvoiceService
    ) {}

    public function index(Request $request): Response
    {
        // Check if this is admin route
        if ($request->routeIs('admin.orders.index')) {
            $orders = \App\Models\Domain\Order\Order::with(['customer', 'items.product', 'items.plan'])
                ->latest()
                ->paginate(15);

            return Inertia::render('admin/orders/Index', [
                'orders' => $orders,
            ]);
        }

        // Customer route
        $customer = $request->user()->customer;

        if (! $customer) {
            return Inertia::render('orders/Index', [
                'orders' => [],
            ]);
        }

        $orders = $this->orderRepository->findByCustomer($customer->id);

        return Inertia::render('orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $order = $this->orderRepository->findByUlid($id);

        if (! $order) {
            abort(404);
        }

        // Check if this is admin route
        if ($request->routeIs('admin.orders.show')) {
            return Inertia::render('admin/orders/Show', [
                'order' => $order->load(['items.product', 'invoices', 'customer']),
            ]);
        }

        return Inertia::render('orders/Show', [
            'order' => $order->load(['items.product', 'invoices']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price_cents' => ['required', 'integer'],
            'items.*.total_cents' => ['required', 'integer'],
            'subtotal_cents' => ['required', 'integer'],
            'total_cents' => ['required', 'integer'],
            'coupon_id' => ['nullable', 'string'],
        ]);

        $order = $this->placeOrderService->execute($validated);

        // Generate invoice
        $invoice = $this->generateInvoiceService->execute([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'currency' => $order->currency,
            'subtotal_cents' => $order->subtotal_cents,
            'discount_cents' => $order->discount_cents,
            'tax_cents' => $order->tax_cents,
            'total_cents' => $order->total_cents,
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order berhasil dibuat.');
    }
}
