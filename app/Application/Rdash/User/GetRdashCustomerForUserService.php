<?php

namespace App\Application\Rdash\User;

use App\Domain\Customer\Contracts\CustomerRepository;
use App\Domain\Rdash\Customer\Contracts\RdashCustomerRepository;
use App\Models\User;

class GetRdashCustomerForUserService
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private RdashCustomerRepository $rdashCustomerRepository
    ) {
    }

    /**
     * Get RDASH customer details untuk user
     */
    public function execute(User $user): ?array
    {
        $customer = $this->customerRepository->findByUser($user->id);

        if (! $customer || ! $customer->rdash_customer_id) {
            return null;
        }

        try {
            $rdashCustomer = $this->rdashCustomerRepository->getById($customer->rdash_customer_id);

            if (! $rdashCustomer) {
                return null;
            }

            return [
                'rdash_customer' => $rdashCustomer->toArray(),
                'sync_status' => $customer->rdash_sync_status,
                'synced_at' => $customer->rdash_synced_at?->toIso8601String(),
                'sync_error' => $customer->rdash_sync_error,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'sync_status' => $customer->rdash_sync_status,
                'synced_at' => $customer->rdash_synced_at?->toIso8601String(),
                'sync_error' => $customer->rdash_sync_error,
            ];
        }
    }
}

