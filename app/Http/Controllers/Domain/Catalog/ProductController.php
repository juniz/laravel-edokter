<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Http\Controllers\Controller;
use App\Domain\Catalog\Contracts\ProductRepository;
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
        $products = \App\Models\Domain\Catalog\Product::with('plans')
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'type' => ['required', 'in:hosting_shared,vps,addon,domain'],
            'status' => ['required', 'in:active,draft,archived'],
            'metadata' => ['nullable', 'array'],
        ]);

        $product = $this->productRepository->create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product berhasil dibuat.');
    }

    public function show(string $id): Response
    {
        $product = $this->productRepository->findByUlid($id);

        if (!$product) {
            abort(404);
        }

        return Inertia::render('admin/products/Show', [
            'product' => $product->load('plans'),
        ]);
    }

    public function edit(string $id): Response
    {
        $product = $this->productRepository->findByUlid($id);

        if (!$product) {
            abort(404);
        }

        return Inertia::render('admin/products/Form', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = $this->productRepository->findByUlid($id);

        if (!$product) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug,' . $id],
            'type' => ['required', 'in:hosting_shared,vps,addon,domain'],
            'status' => ['required', 'in:active,draft,archived'],
            'metadata' => ['nullable', 'array'],
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $product = $this->productRepository->findByUlid($id);

        if (!$product) {
            abort(404);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product berhasil dihapus.');
    }
}
