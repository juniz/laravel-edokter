<?php

namespace App\Domain\Rdash\BareMetal\Contracts;

use App\Domain\Rdash\BareMetal\ValueObjects\BareMetalProduct;
use App\Domain\Rdash\BareMetal\ValueObjects\BareMetalOrder;

interface BareMetalRepository
{
    /**
     * Get list all bare metal products
     *
     * @param array<string, mixed> $filters
     * @return array<int, BareMetalProduct>
     */
    public function getProducts(array $filters = []): array;

    /**
     * Get list all bare metal products with prices
     *
     * @param array<string, mixed> $filters
     * @return array<int, BareMetalProduct>
     */
    public function getProductsWithPrices(array $filters = []): array;

    /**
     * Get list operating systems for bare metal product
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOperatingSystems(int $bareMetalProductId): array;

    /**
     * Get list all bare metal orders
     *
     * @param array<string, mixed> $filters
     * @return array<int, BareMetalOrder>
     */
    public function getOrders(array $filters = []): array;

    /**
     * Get bare metal order by id
     */
    public function getOrderById(int $bareMetalOrderId): ?BareMetalOrder;

    /**
     * Order bare metal
     *
     * @param array<string, mixed> $data
     */
    public function order(array $data): BareMetalOrder;

    /**
     * Suspend bare metal
     */
    public function suspend(int $bareMetalOrderId): bool;

    /**
     * Unsuspend bare metal
     */
    public function unsuspend(int $bareMetalOrderId): bool;

    /**
     * Update bare metal state (on, off, reset)
     */
    public function updateState(int $bareMetalOrderId, string $state): bool;

    /**
     * Rebuild bare metal OS
     */
    public function rebuild(int $bareMetalOrderId, string $os): bool;
}

