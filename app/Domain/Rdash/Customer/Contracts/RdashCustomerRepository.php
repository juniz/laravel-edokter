<?php

namespace App\Domain\Rdash\Customer\Contracts;

use App\Domain\Rdash\Customer\ValueObjects\RdashCustomer;

interface RdashCustomerRepository
{
    /**
     * Get list all customers
     *
     * @param array<string, mixed> $filters
     * @return array<int, RdashCustomer>
     */
    public function getAll(array $filters = []): array;

    /**
     * Get customer by id
     */
    public function getById(int $customerId): ?RdashCustomer;

    /**
     * Find customer by email
     */
    public function findByEmail(string $email): ?RdashCustomer;

    /**
     * Create new customer
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): RdashCustomer;

    /**
     * Update customer by id
     *
     * @param array<string, mixed> $data
     */
    public function update(int $customerId, array $data): RdashCustomer;

    /**
     * Delete customer by id
     */
    public function delete(int $customerId): bool;
}

