<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\Ssl\Contracts\SslRepository as SslRepositoryContract;
use App\Domain\Rdash\Ssl\ValueObjects\SslOrder;
use App\Domain\Rdash\Ssl\ValueObjects\SslProduct;
use App\Infrastructure\Rdash\HttpClient;

class SslRepository implements SslRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {}

    public function getProducts(array $filters = []): array
    {
        $response = $this->client->get('/ssl/', $filters);

        // Handle RDASH API response structure dengan pagination
        // Response: {data: [...], links: {...}, meta: {...}}
        $products = $response['data'] ?? [];

        return array_map(
            fn (array $product) => SslProduct::fromArray($product),
            $products
        );
    }

    /**
     * Get products dengan pagination info
     *
     * @param  array<string, mixed>  $filters
     * @return array{products: array<int, SslProduct>, links: array<string, mixed>, meta: array<string, mixed>}
     */
    public function getProductsWithPagination(array $filters = []): array
    {
        $response = $this->client->get('/ssl/', $filters);

        $products = array_map(
            fn (array $product) => SslProduct::fromArray($product),
            $response['data'] ?? []
        );

        return [
            'products' => $products,
            'links' => $response['links'] ?? [],
            'meta' => $response['meta'] ?? [],
        ];
    }

    public function getProductsWithPrices(array $filters = []): array
    {
        $data = $this->client->get('/ssl/prices', $filters);
        $products = $data['data'] ?? [];

        return array_map(
            fn (array $product) => SslProduct::fromArray($product),
            $products
        );
    }

    public function getOrders(array $filters = []): array
    {
        $data = $this->client->get('/ssl/orders/', $filters);
        $orders = $data['data'] ?? [];

        return array_map(
            fn (array $order) => SslOrder::fromArray($order),
            $orders
        );
    }

    public function getOrderById(int $sslOrderId): ?SslOrder
    {
        $data = $this->client->get("/ssl/orders/{$sslOrderId}");

        return empty($data) ? null : SslOrder::fromArray($data);
    }

    public function generateCsr(array $data): string
    {
        $response = $this->client->post('/ssl/csr/generate', $data);

        return $response['csr'] ?? '';
    }

    public function buy(array $data): SslOrder
    {
        $response = $this->client->post('/ssl/orders/', $data);

        return SslOrder::fromArray($response);
    }

    public function changeValidationMethod(int $sslOrderId, array $data): bool
    {
        $this->client->put("/ssl/orders/{$sslOrderId}", $data);

        return true;
    }

    public function revalidate(int $sslOrderId): bool
    {
        $this->client->post("/ssl/orders/{$sslOrderId}/revalidate");

        return true;
    }

    public function reissue(int $sslOrderId, array $data): bool
    {
        $this->client->post("/ssl/orders/{$sslOrderId}/reissue", $data);

        return true;
    }

    public function download(int $sslOrderId): array
    {
        return $this->client->get("/ssl/orders/{$sslOrderId}/download");
    }

    public function cancel(int $sslOrderId): bool
    {
        $this->client->delete("/ssl/orders/{$sslOrderId}");

        return true;
    }
}
