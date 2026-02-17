<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Domain\Catalog\ProductType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $query = ProductType::query()
            ->orderBy('display_order')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $types = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/product-types/Index', [
            'types' => $types,
            'filters' => [
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/product-types/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:product_types,slug'],
            'status' => ['required', 'in:active,draft,archived'],
            'icon' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer'],
            'metadata' => ['nullable', 'array'],
        ]);

        ProductType::create([
            ...$validated,
            'display_order' => $validated['display_order'] ?? 0,
        ]);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Tipe produk berhasil dibuat.');
    }

    public function edit(string $id): Response
    {
        $type = ProductType::findOrFail($id);

        return Inertia::render('admin/product-types/Form', [
            'type' => $type,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $type = ProductType::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:product_types,slug,'.$id],
            'status' => ['required', 'in:active,draft,archived'],
            'icon' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer'],
            'metadata' => ['nullable', 'array'],
        ]);

        $type->update([
            ...$validated,
            'display_order' => $validated['display_order'] ?? 0,
        ]);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Tipe produk berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $type = ProductType::withCount('products')->findOrFail($id);

        if ($type->products_count > 0) {
            return redirect()->route('admin.product-types.index')
                ->with('error', 'Tipe produk tidak bisa dihapus karena masih dipakai produk.');
        }

        $type->delete();

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Tipe produk berhasil dihapus.');
    }
}

