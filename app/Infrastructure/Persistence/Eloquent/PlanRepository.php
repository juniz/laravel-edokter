<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\Contracts\PlanRepository as PlanRepositoryContract;
use App\Models\Domain\Catalog\Plan;

class PlanRepository implements PlanRepositoryContract
{
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    public function findByUlid(string $id): ?Plan
    {
        return Plan::find($id);
    }

    public function findByCode(string $code): ?Plan
    {
        return Plan::where('code', $code)->first();
    }

    public function findByProduct(string $productId): array
    {
        return Plan::where('product_id', $productId)->get()->all();
    }
}

