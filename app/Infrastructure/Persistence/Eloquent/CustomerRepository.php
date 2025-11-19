<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Customer\Contracts\CustomerRepository as CustomerRepositoryContract;
use App\Events\CustomerCreated;
use App\Events\CustomerUpdated;
use App\Models\Domain\Customer\Customer;

class CustomerRepository implements CustomerRepositoryContract
{
    public function create(array $data): Customer
    {
        $customer = Customer::create($data);
        
        // Dispatch CustomerCreated event
        event(new CustomerCreated($customer));
        
        return $customer;
    }

    public function findByUlid(string $id): ?Customer
    {
        return Customer::find($id);
    }

    public function findByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }

    public function findByUser(string $userId): ?Customer
    {
        return Customer::where('user_id', $userId)->first();
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        
        // Dispatch CustomerUpdated event
        event(new CustomerUpdated($customer));
        
        return $customer;
    }
}

