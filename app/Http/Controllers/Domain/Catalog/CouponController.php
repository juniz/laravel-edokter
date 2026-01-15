<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Domain\Catalog\Coupon;
use App\Models\Domain\Catalog\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    /**
     * Display listing of coupons
     */
    public function index(Request $request): Response
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $query->where('code', 'like', '%'.$request->search.'%');
        }

        $coupons = $query->latest()->paginate(15);

        return Inertia::render('admin/coupons/Index', [
            'coupons' => $coupons,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show form for creating new coupon
     */
    public function create(): Response
    {
        $products = Product::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('admin/coupons/Form', [
            'products' => $products,
        ]);
    }

    /**
     * Store newly created coupon
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:coupons,code'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'applicable_product_ids' => ['nullable', 'array'],
            'applicable_product_ids.*' => ['string', 'exists:products,id'],
        ]);

        $coupon = Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon berhasil dibuat.');
    }

    /**
     * Show form for editing coupon
     */
    public function edit(string $id): Response
    {
        $coupon = Coupon::findOrFail($id);
        $products = Product::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('admin/coupons/Form', [
            'coupon' => $coupon,
            'products' => $products,
        ]);
    }

    /**
     * Update coupon
     */
    public function update(Request $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:coupons,code,'.$id],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'applicable_product_ids' => ['nullable', 'array'],
            'applicable_product_ids.*' => ['string', 'exists:products,id'],
        ]);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon berhasil diperbarui.');
    }

    /**
     * Delete coupon
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon berhasil dihapus.');
    }
}
