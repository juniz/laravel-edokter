<?php

namespace App\Domain\Catalog\Contracts;

use App\Models\Domain\Catalog\Plan;

interface PlanRepository
{
    public function create(array $data): Plan;
    public function findByUlid(string $id): ?Plan;
    public function findByCode(string $code): ?Plan;
    public function findByProduct(string $productId): array;
}

