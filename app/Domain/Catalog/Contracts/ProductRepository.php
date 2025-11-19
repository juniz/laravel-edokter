<?php

namespace App\Domain\Catalog\Contracts;

use App\Models\Domain\Catalog\Product;

interface ProductRepository
{
    public function create(array $data): Product;
    public function findByUlid(string $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function findAllActive(): array;
}
