<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\Contracts\ProductRepository as ProductRepositoryContract;
use App\Models\Domain\Catalog\Product;

class ProductRepository implements ProductRepositoryContract
{
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function findByUlid(string $id): ?Product
    {
        return Product::find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->where('status', 'active')->first();
    }

    public function findAllActive(): array
    {
        return Product::where('status', 'active')->get()->all();
    }
}
