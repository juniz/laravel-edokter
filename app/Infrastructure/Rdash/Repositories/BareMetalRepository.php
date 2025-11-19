<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\BareMetal\Contracts\BareMetalRepository as BareMetalRepositoryContract;
use App\Domain\Rdash\BareMetal\ValueObjects\BareMetalOrder;
use App\Domain\Rdash\BareMetal\ValueObjects\BareMetalProduct;
use App\Infrastructure\Rdash\HttpClient;

class BareMetalRepository implements BareMetalRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {
    }

    public function getProducts(array $filters = []): array
    {
        $data = $this->client->get('/baremetals/', $filters);
        $products = $data['data'] ?? [];

        return array_map(
            fn (array $product) => BareMetalProduct::fromArray($product),
            $products
        );
    }

    public function getProductsWithPrices(array $filters = []): array
    {
        $data = $this->client->get('/baremetals/prices', $filters);
        $products = $data['data'] ?? [];

        return array_map(
            fn (array $product) => BareMetalProduct::fromArray($product),
            $products
        );
    }

    public function getOperatingSystems(int $bareMetalProductId): array
    {
        return $this->client->get("/baremetals/os/{$bareMetalProductId}");
    }

    public function getOrders(array $filters = []): array
    {
        $data = $this->client->get('/baremetals/orders/', $filters);
        $orders = $data['data'] ?? [];

        return array_map(
            fn (array $order) => BareMetalOrder::fromArray($order),
            $orders
        );
    }

    public function getOrderById(int $bareMetalOrderId): ?BareMetalOrder
    {
        $data = $this->client->get("/baremetals/orders/{$bareMetalOrderId}");

        return empty($data) ? null : BareMetalOrder::fromArray($data);
    }

    public function order(array $data): BareMetalOrder
    {
        $response = $this->client->post('/baremetals/orders/', $data);

        return BareMetalOrder::fromArray($response);
    }

    public function suspend(int $bareMetalOrderId): bool
    {
        $this->client->put("/baremetals/orders/{$bareMetalOrderId}/suspend");

        return true;
    }

    public function unsuspend(int $bareMetalOrderId): bool
    {
        $this->client->delete("/baremetals/orders/{$bareMetalOrderId}/unsuspend");

        return true;
    }

    public function updateState(int $bareMetalOrderId, string $state): bool
    {
        $this->client->put("/baremetals/orders/{$bareMetalOrderId}/state", [
            'state' => $state,
        ]);

        return true;
    }

    public function rebuild(int $bareMetalOrderId, string $os): bool
    {
        $this->client->post("/baremetals/orders/{$bareMetalOrderId}/rebuild", [
            'os' => $os,
        ]);

        return true;
    }
}

