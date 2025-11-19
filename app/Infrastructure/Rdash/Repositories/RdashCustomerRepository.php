<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\Customer\Contracts\RdashCustomerRepository as RdashCustomerRepositoryContract;
use App\Domain\Rdash\Customer\ValueObjects\RdashCustomer;
use App\Infrastructure\Rdash\HttpClient;

class RdashCustomerRepository implements RdashCustomerRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {
    }

    public function getAll(array $filters = []): array
    {
        $data = $this->client->get('/customers', $filters);
        $customers = $data['data'] ?? [];

        return array_map(
            fn (array $customer) => RdashCustomer::fromArray($customer),
            $customers
        );
    }

    public function getById(int $customerId): ?RdashCustomer
    {
        $response = $this->client->get("/customers/{$customerId}");

        // Handle RDASH API response structure: {success: true, data: {...}, message: "Success"}
        $data = $response['data'] ?? $response;

        if (empty($data) || !isset($data['id'])) {
            return null;
        }

        return RdashCustomer::fromArray($data);
    }

    public function findByEmail(string $email): ?RdashCustomer
    {
        // Cari customer berdasarkan email dari list customers
        $customers = $this->getAll(['email' => $email]);

        // Jika ditemukan, return yang pertama
        if (!empty($customers)) {
            return $customers[0];
        }

        return null;
    }

    public function create(array $data): RdashCustomer
    {
        $response = $this->client->post('/customers', $data);

        // Handle RDASH API response structure: {success: true, data: {...}, message: "Success"}
        $customerData = $response['data'] ?? $response;

        return RdashCustomer::fromArray($customerData);
    }

    public function update(int $customerId, array $data): RdashCustomer
    {
        $response = $this->client->put("/customers/{$customerId}", $data);

        // Handle RDASH API response structure: {success: true, data: {...}, message: "Success"}
        $customerData = $response['data'] ?? $response;

        return RdashCustomer::fromArray($customerData);
    }

    public function delete(int $customerId): bool
    {
        $this->client->delete("/customers/{$customerId}");

        return true;
    }
}

