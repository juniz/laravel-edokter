<?php

namespace App\Application\Rdash\User;

use App\Domain\Customer\Contracts\CustomerRepository;
use App\Domain\Rdash\Customer\Contracts\RdashCustomerRepository;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UpdateRdashCustomerService
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private RdashCustomerRepository $rdashCustomerRepository
    ) {
    }

    /**
     * Update RDASH customer untuk user
     * 
     * @return array{success: bool, message: string, rdash_customer?: array}
     */
    public function execute(User $user, array $rdashCustomerData): array
    {
        $customer = null;
        
        try {
            $customer = $this->customerRepository->findByUser($user->id);

            if (! $customer || ! $customer->rdash_customer_id) {
                return [
                    'success' => false,
                    'message' => 'User tidak memiliki customer di RDASH. Silakan sync terlebih dahulu.',
                ];
            }

            // Prepare data untuk update di RDASH
            $updateData = $this->prepareUpdateData($rdashCustomerData);

            // Update customer di RDASH
            $rdashCustomer = $this->rdashCustomerRepository->update(
                $customer->rdash_customer_id,
                $updateData
            );

            // Update customer lokal juga untuk konsistensi
            $customer->update([
                'name' => $rdashCustomerData['name'] ?? $customer->name,
                'email' => $rdashCustomerData['email'] ?? $customer->email,
                'phone' => $rdashCustomerData['voice'] ?? $rdashCustomerData['phone'] ?? $customer->phone,
                'organization' => $rdashCustomerData['organization'] ?? $customer->organization,
                'street_1' => $rdashCustomerData['street_1'] ?? $customer->street_1,
                'street_2' => $rdashCustomerData['street_2'] ?? $customer->street_2,
                'city' => $rdashCustomerData['city'] ?? $customer->city,
                'state' => $rdashCustomerData['state'] ?? $customer->state,
                'country_code' => $rdashCustomerData['country_code'] ?? $customer->country_code,
                'postal_code' => $rdashCustomerData['postal_code'] ?? $customer->postal_code,
                'fax' => $rdashCustomerData['fax'] ?? $customer->fax,
                'rdash_synced_at' => now(),
                'rdash_sync_status' => 'synced',
                'rdash_sync_error' => null,
            ]);

            return [
                'success' => true,
                'message' => 'Customer RDASH berhasil di-update',
                'rdash_customer' => $rdashCustomer->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Update RDASH Customer failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($customer) {
                $customer->update([
                    'rdash_sync_status' => 'failed',
                    'rdash_sync_error' => $e->getMessage(),
                ]);
            }

            return [
                'success' => false,
                'message' => 'Gagal update customer RDASH: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare data untuk update di RDASH API
     * Sesuai dengan requirement RDASH API untuk update
     */
    private function prepareUpdateData(array $data): array
    {
        $updateData = [];

        // Field yang bisa di-update di RDASH
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        if (isset($data['organization'])) {
            $updateData['organization'] = $data['organization'];
        }
        if (isset($data['street_1'])) {
            $updateData['street_1'] = $data['street_1'];
        }
        if (isset($data['street_2'])) {
            $updateData['street_2'] = $data['street_2'];
        }
        if (isset($data['city'])) {
            $updateData['city'] = $data['city'];
        }
        if (isset($data['state'])) {
            $updateData['state'] = $data['state'];
        }
        if (isset($data['country_code'])) {
            $updateData['country_code'] = $data['country_code'];
        }
        if (isset($data['postal_code'])) {
            $updateData['postal_code'] = $data['postal_code'];
        }
        if (isset($data['voice']) || isset($data['phone'])) {
            $updateData['voice'] = $data['voice'] ?? $data['phone'];
        }
        if (isset($data['fax'])) {
            $updateData['fax'] = $data['fax'];
        }

        return $updateData;
    }
}

