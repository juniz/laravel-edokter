<?php

namespace App\Domain\Customer\Contracts;

use App\Models\Domain\Customer\Customer;

interface CustomerRepository
{
    public function create(array $data): Customer;
    public function findByUlid(string $id): ?Customer;
    public function findByEmail(string $email): ?Customer;
    public function findByUser(string $userId): ?Customer;
    public function update(Customer $customer, array $data): Customer;
}

