<?php

namespace App\Domain\Rdash\Ssl\Contracts;

use App\Domain\Rdash\Ssl\ValueObjects\SslProduct;
use App\Domain\Rdash\Ssl\ValueObjects\SslOrder;

interface SslRepository
{
    /**
     * Get list all SSL products
     *
     * @param array<string, mixed> $filters
     * @return array<int, SslProduct>
     */
    public function getProducts(array $filters = []): array;

    /**
     * Get list all SSL products with prices
     *
     * @param array<string, mixed> $filters
     * @return array<int, SslProduct>
     */
    public function getProductsWithPrices(array $filters = []): array;

    /**
     * Get list all SSL orders
     *
     * @param array<string, mixed> $filters
     * @return array<int, SslOrder>
     */
    public function getOrders(array $filters = []): array;

    /**
     * Get SSL order by id
     */
    public function getOrderById(int $sslOrderId): ?SslOrder;

    /**
     * Generate CSR
     *
     * @param array<string, mixed> $data
     */
    public function generateCsr(array $data): string;

    /**
     * Buy SSL
     *
     * @param array<string, mixed> $data
     */
    public function buy(array $data): SslOrder;

    /**
     * Change SSL validation method
     *
     * @param array<string, mixed> $data
     */
    public function changeValidationMethod(int $sslOrderId, array $data): bool;

    /**
     * Revalidate SSL
     */
    public function revalidate(int $sslOrderId): bool;

    /**
     * Reissue SSL
     *
     * @param array<string, mixed> $data
     */
    public function reissue(int $sslOrderId, array $data): bool;

    /**
     * Download SSL certificate
     */
    public function download(int $sslOrderId): array;

    /**
     * Cancel SSL order
     */
    public function cancel(int $sslOrderId): bool;
}

