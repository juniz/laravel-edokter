<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Domain\Catalog\Contracts\ProductRepository;
use App\Http\Controllers\Controller;
use App\Models\Domain\Catalog\ProductType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function index(): Response
    {
        $products = \App\Models\Domain\Catalog\Product::with(['features', 'productType'])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        $productTypes = ProductType::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'status']);

        return Inertia::render('admin/products/Form', [
            'productTypes' => $productTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'product_type_id' => ['required', 'string', 'exists:product_types,id'],
            'status' => ['required', 'in:active,draft,archived'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'setup_fee_cents' => ['nullable', 'integer', 'min:0'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'annual_discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'duration_1_month_enabled' => ['required', 'boolean'],
            'duration_12_months_enabled' => ['required', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'features.*.key' => ['required', 'string'],
            'features.*.value' => ['required', 'string'],
            'features.*.label' => ['nullable', 'string'],
            'features.*.unit' => ['nullable', 'string'],
            'features.*.display_order' => ['nullable', 'integer'],
        ]);

        $product = $this->productRepository->create($validated);

        // Save features
        if ($request->has('features') && is_array($request->features)) {
            foreach ($request->features as $index => $feature) {
                \App\Models\Domain\Catalog\ProductFeature::create([
                    'product_id' => $product->id,
                    'key' => $feature['key'],
                    'value' => $feature['value'],
                    'label' => $feature['label'] ?? null,
                    'unit' => $feature['unit'] ?? null,
                    'display_order' => $feature['display_order'] ?? $index,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product berhasil dibuat.');
    }

    public function show(string $id): Response
    {
        $product = $this->productRepository->findByUlid($id);

        if (! $product) {
            abort(404);
        }

        return Inertia::render('admin/products/Show', [
            'product' => $product->load(['features', 'productType']),
        ]);
    }

    public function edit(string $id): Response
    {
        $product = $this->productRepository->findByUlid($id);

        if (! $product) {
            abort(404);
        }

        $productTypes = ProductType::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'status']);

        return Inertia::render('admin/products/Form', [
            'product' => $product->load(['features', 'productType']),
            'productTypes' => $productTypes,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = $this->productRepository->findByUlid($id);

        if (! $product) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug,'.$id],
            'product_type_id' => ['required', 'string', 'exists:product_types,id'],
            'status' => ['required', 'in:active,draft,archived'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'setup_fee_cents' => ['nullable', 'integer', 'min:0'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'annual_discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'duration_1_month_enabled' => ['required', 'boolean'],
            'duration_12_months_enabled' => ['required', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'features.*.key' => ['required', 'string'],
            'features.*.value' => ['required', 'string'],
            'features.*.label' => ['nullable', 'string'],
            'features.*.unit' => ['nullable', 'string'],
            'features.*.display_order' => ['nullable', 'integer'],
        ]);

        $product->update($validated);

        // Update features - delete existing and create new ones
        $product->features()->delete();
        if ($request->has('features') && is_array($request->features)) {
            foreach ($request->features as $index => $feature) {
                \App\Models\Domain\Catalog\ProductFeature::create([
                    'product_id' => $product->id,
                    'key' => $feature['key'],
                    'value' => $feature['value'],
                    'label' => $feature['label'] ?? null,
                    'unit' => $feature['unit'] ?? null,
                    'display_order' => $feature['display_order'] ?? $index,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $product = $this->productRepository->findByUlid($id);

        if (! $product) {
            abort(404);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product berhasil dihapus.');
    }
}
