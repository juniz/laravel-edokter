<?php

namespace App\Http\Controllers\Domain\Order;

use App\Application\Order\CartService;
use App\Application\Order\CheckoutCartService;
use App\Application\Order\ValidateCouponService;
use App\Http\Controllers\Controller;
use App\Models\Domain\Order\Cart;
use App\Models\Domain\Order\CartItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CheckoutCartService $checkoutCartService,
        private ValidateCouponService $validateCouponService
    ) {}

    /**
     * Display cart page
     */
    public function index(Request $request): Response
    {
        $customer = $request->user()->customer;

        if (! $customer) {
            abort(403, 'Customer profile tidak ditemukan');
        }

        $cart = $this->cartService->getOrCreateCart($customer);
        $cart->load('items.product');

        return Inertia::render('cart/Index', [
            'cart' => [
                'id' => $cart->id,
                'currency' => $cart->currency,
                'totals' => $cart->totals_json ?? [
                    'subtotal' => 0,
                    'setup_fee' => 0,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 0,
                ],
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'slug' => $item->product->slug,
                        ],
                        'qty' => $item->qty,
                        'unit_price_cents' => $item->unit_price_cents,
                        'total_cents' => $item->unit_price_cents * $item->qty,
                        'meta' => $item->meta ?? [],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'string'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'meta' => ['nullable', 'array'],
        ]);

        $customer = $request->user()->customer;

        if (! $customer) {
            return redirect()->back()
                ->withErrors(['error' => 'Customer profile tidak ditemukan. Silakan lengkapi profil Anda.']);
        }

        try {
            $cartItem = $this->cartService->addItem($customer, $request->only([
                'product_id',
                'qty',
                'meta',
            ]));

            return redirect()->back()
                ->with('success', 'Item berhasil ditambahkan ke cart.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update cart item
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'qty' => ['nullable', 'integer', 'min:1'],
            'meta' => ['nullable', 'array'],
        ]);

        $cartItem = CartItem::find($id);

        if (! $cartItem) {
            abort(404);
        }

        // Check ownership
        if ($request->user()->customer->id !== $cartItem->cart->customer_id) {
            abort(403);
        }

        try {
            $this->cartService->updateItem($cartItem, $request->only(['qty', 'meta']));

            return redirect()->back()
                ->with('success', 'Cart berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, string $id)
    {
        $cartItem = CartItem::find($id);

        if (! $cartItem) {
            abort(404);
        }

        // Check ownership
        if ($request->user()->customer->id !== $cartItem->cart->customer_id) {
            abort(403);
        }

        try {
            $this->cartService->removeItem($cartItem);

            return redirect()->back()
                ->with('success', 'Item berhasil dihapus dari cart.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Clear cart
     */
    public function clear(Request $request)
    {
        $customer = $request->user()->customer;

        if (! $customer) {
            abort(403, 'Customer profile tidak ditemukan');
        }

        $cart = $this->cartService->getOrCreateCart($customer);

        try {
            $this->cartService->clearCart($cart);

            return redirect()->back()
                ->with('success', 'Cart berhasil dikosongkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Validate promo code
     */
    public function validatePromo(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $customer = $request->user()->customer;

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer profile tidak ditemukan',
            ], 403);
        }

        $cart = $this->cartService->getOrCreateCart($customer);
        $cart->load('items.product');
        $productIds = $cart->items->pluck('product_id')->toArray();

        $validation = $this->validateCouponService->validate($request->code, $productIds);

        if (! $validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message'] ?? 'Kode promo tidak valid',
            ]);
        }

        // Apply coupon to cart
        $result = $this->cartService->applyCoupon($cart, $request->code);

        if ($result['success']) {
            $cart->refresh();
            $cart->load('coupon');

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'discount' => $result['discount'],
                'coupon' => [
                    'code' => $cart->coupon->code,
                    'type' => $cart->coupon->type,
                    'value' => $cart->coupon->value,
                ],
                'totals' => $cart->totals_json,
            ]);
        }

        return response()->json($result);
    }

    /**
     * Remove promo code from cart
     */
    public function removePromo(Request $request)
    {
        $customer = $request->user()->customer;

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer profile tidak ditemukan',
            ], 403);
        }

        $cart = $this->cartService->getOrCreateCart($customer);
        $this->cartService->removeCoupon($cart);

        $cart->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Kode promo berhasil dihapus',
            'totals' => $cart->totals_json,
        ]);
    }

    /**
     * Checkout cart
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $customer = $request->user()->customer;

        if (! $customer) {
            return redirect()->back()
                ->withErrors(['error' => 'Customer profile tidak ditemukan. Silakan lengkapi profil Anda.']);
        }

        $cart = $this->cartService->getOrCreateCart($customer);

        if ($cart->items->isEmpty()) {
            return redirect()->back()
                ->withErrors(['error' => 'Cart kosong. Silakan tambahkan item terlebih dahulu.']);
        }

        try {
            $payment = $this->checkoutCartService->execute($cart, [
                'payment_method' => $request->payment_method,
            ]);

            return redirect()->route('customer.payments.show', $payment->id)
                ->with('success', 'Pembayaran berhasil dibuat. Silakan selesaikan pembayaran Anda.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
