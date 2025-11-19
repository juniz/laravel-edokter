<?php

namespace App\Application\Rdash\User;

use App\Domain\Customer\Contracts\CustomerRepository;
use App\Domain\Rdash\Customer\Contracts\RdashCustomerRepository;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CreateUserWithRdashCustomerService
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private RdashCustomerRepository $rdashCustomerRepository
    ) {
    }

    /**
     * Create user sekaligus create customer di RDASH
     * 
     * @param array<string, mixed> $userData
     * @param array<string, mixed> $customerData
     * @return array{user: User, customer: \App\Models\Domain\Customer\Customer, rdash_customer_id?: int}
     */
    public function execute(array $userData, array $customerData): array
    {
        try {
            // Create user
            $user = User::create($userData);

            // Create customer lokal dengan data RDASH
            $customer = $this->customerRepository->create([
                'user_id' => $user->id,
                'name' => $customerData['name'] ?? $user->name,
                'email' => $customerData['email'] ?? $user->email,
                'phone' => $customerData['phone'] ?? null,
                'tax_number' => $customerData['tax_number'] ?? null,
                'billing_address_json' => $customerData['billing_address'] ?? null,
                // RDASH required fields
                'organization' => $customerData['organization'] ?? $customerData['name'] ?? $user->name,
                'street_1' => $customerData['street_1'] ?? 'Not Provided',
                'street_2' => $customerData['street_2'] ?? null,
                'city' => $customerData['city'] ?? 'Jakarta',
                'state' => $customerData['state'] ?? null,
                'country_code' => $customerData['country_code'] ?? 'ID',
                'postal_code' => $customerData['postal_code'] ?? '00000',
                'fax' => $customerData['fax'] ?? null,
                'rdash_sync_status' => 'pending',
            ]);

            // Create customer di RDASH
            $rdashCustomer = $this->rdashCustomerRepository->create(
                $this->prepareRdashCustomerData($customerData, $user)
            );

            // Update customer dengan rdash_customer_id
            $customer->update([
                'rdash_customer_id' => $rdashCustomer->id,
                'rdash_synced_at' => now(),
                'rdash_sync_status' => 'synced',
            ]);

            return [
                'user' => $user,
                'customer' => $customer,
                'rdash_customer_id' => $rdashCustomer->id,
            ];
        } catch (\Exception $e) {
            Log::error('Create User with RDASH Customer failed', [
                'user_data' => $userData,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Prepare customer data untuk RDASH API
     * Sesuai dengan requirement RDASH API: semua field required harus ada
     */
    private function prepareRdashCustomerData(array $customerData, User $user): array
    {
        $billingAddress = $customerData['billing_address'] ?? [];

        // Pastikan semua required fields ada dengan default values jika tidak ada
        $organization = $customerData['organization'] 
            ?? $billingAddress['organization'] 
            ?? $customerData['name'] 
            ?? $user->name 
            ?? 'N/A';

        $street1 = $customerData['street_1'] 
            ?? $billingAddress['street_1'] 
            ?? 'Not Provided';

        $city = $customerData['city'] 
            ?? $billingAddress['city'] 
            ?? 'Jakarta';

        $countryCode = $customerData['country_code'] 
            ?? $billingAddress['country_code'] 
            ?? 'ID';

        $postalCode = $customerData['postal_code'] 
            ?? $billingAddress['postal_code'] 
            ?? '00000';

        $voice = $customerData['phone'] 
            ?? $billingAddress['phone'] 
            ?? '081234567890';

        // Validasi voice sesuai requirement RDASH (min 9, max 20)
        if (strlen($voice) < 9) {
            $voice = str_pad($voice, 9, '0', STR_PAD_RIGHT);
        }
        if (strlen($voice) > 20) {
            $voice = substr($voice, 0, 20);
        }

        return [
            'name' => $customerData['name'] ?? $user->name,
            'email' => $customerData['email'] ?? $user->email,
            'password' => $customerData['password'] ?? 'TempPassword123!',
            'password_confirmation' => $customerData['password_confirmation'] ?? ($customerData['password'] ?? 'TempPassword123!'),
            'organization' => $organization,
            'street_1' => $street1,
            'street_2' => $customerData['street_2'] ?? $billingAddress['street_2'] ?? null,
            'city' => $city,
            'state' => $customerData['state'] ?? $billingAddress['state'] ?? null,
            'country_code' => $countryCode,
            'postal_code' => $postalCode,
            'voice' => $voice,
            'fax' => $customerData['fax'] ?? $billingAddress['fax'] ?? null,
        ];
    }
}

