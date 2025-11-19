<?php

namespace App\Application\Rdash\User;

use App\Domain\Customer\Contracts\CustomerRepository;
use App\Domain\Rdash\Customer\Contracts\RdashCustomerRepository;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SyncUserToRdashService
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private RdashCustomerRepository $rdashCustomerRepository
    ) {
    }

    /**
     * Sync user ke RDASH customer
     * 
     * @return array{success: bool, message: string, rdash_customer_id?: int}
     */
    public function execute(User $user, bool $createCustomerIfNotExists = true): array
    {
        try {
            // Cek apakah user punya customer
            $customer = $this->customerRepository->findByUser($user->id);

            if (! $customer && ! $createCustomerIfNotExists) {
                return [
                    'success' => false,
                    'message' => 'User tidak memiliki customer profile. Silakan buat customer terlebih dahulu.',
                ];
            }

            // Jika belum punya customer, buat customer lokal dulu dengan default values untuk RDASH
            if (! $customer) {
                $customer = $this->customerRepository->create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'organization' => $user->name,
                    'street_1' => 'Not Provided',
                    'city' => 'Jakarta',
                    'country_code' => 'ID',
                    'postal_code' => '00000',
                    'phone' => '081234567890',
                    'rdash_sync_status' => 'pending',
                ]);
            }

            // Jika sudah punya rdash_customer_id, update di RDASH
            if ($customer->rdash_customer_id) {
                $rdashCustomer = $this->rdashCustomerRepository->update(
                    $customer->rdash_customer_id,
                    $this->prepareCustomerData($customer, $user)
                );

                $customer->update([
                    'rdash_synced_at' => now(),
                    'rdash_sync_status' => 'synced',
                    'rdash_sync_error' => null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Customer berhasil di-update di RDASH',
                    'rdash_customer_id' => $rdashCustomer->id,
                ];
            }

            // Jika belum punya rdash_customer_id, create di RDASH
            try {
                $rdashCustomer = $this->rdashCustomerRepository->create(
                    $this->prepareCustomerData($customer, $user)
                );

                $customer->update([
                    'rdash_customer_id' => $rdashCustomer->id,
                    'rdash_synced_at' => now(),
                    'rdash_sync_status' => 'synced',
                    'rdash_sync_error' => null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Customer berhasil dibuat di RDASH',
                    'rdash_customer_id' => $rdashCustomer->id,
                ];
            } catch (\Exception $createException) {
                // Handle kasus khusus: email sudah ada di RDASH
                // Ini bisa terjadi jika customer sudah dibuat sebelumnya tapi rdash_customer_id belum tersimpan
                $errorMessage = $createException->getMessage();
                
                if (str_contains($errorMessage, 'Email has already on this reseller') || 
                    str_contains($errorMessage, 'email') && str_contains($errorMessage, 'already')) {
                    
                    Log::info('Customer email already exists in RDASH, attempting to find existing customer', [
                        'user_id' => $user->id,
                        'email' => $customer->email ?? $user->email,
                    ]);

                    // Coba cari customer yang sudah ada berdasarkan email
                    $existingRdashCustomer = $this->rdashCustomerRepository->findByEmail(
                        $customer->email ?? $user->email
                    );

                    if ($existingRdashCustomer) {
                        // Customer ditemukan, update rdash_customer_id dan status
                        $customer->update([
                            'rdash_customer_id' => $existingRdashCustomer->id,
                            'rdash_synced_at' => now(),
                            'rdash_sync_status' => 'synced',
                            'rdash_sync_error' => null,
                        ]);

                        Log::info('Found existing RDASH customer and updated local record', [
                            'user_id' => $user->id,
                            'rdash_customer_id' => $existingRdashCustomer->id,
                        ]);

                        return [
                            'success' => true,
                            'message' => 'Customer sudah ada di RDASH, data telah di-link',
                            'rdash_customer_id' => $existingRdashCustomer->id,
                        ];
                    }

                    // Jika tidak ditemukan, throw exception asli
                    throw $createException;
                }

                // Jika bukan error email sudah ada, throw exception asli
                throw $createException;
            }
        } catch (\Exception $e) {
            Log::error('Sync User to RDASH failed', [
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
                'message' => 'Gagal sync ke RDASH: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare customer data untuk RDASH API
     * Sesuai dengan requirement RDASH API: semua field required harus ada
     */
    private function prepareCustomerData($customer, User $user): array
    {
        $billingAddress = $customer->billing_address_json ?? [];

        // Gunakan data dari kolom langsung jika ada, fallback ke billing_address_json, lalu default
        $organization = $customer->organization 
            ?? $billingAddress['organization'] 
            ?? $customer->name 
            ?? $user->name 
            ?? 'N/A';

        $street1 = $customer->street_1 
            ?? $billingAddress['street_1'] 
            ?? 'Not Provided';

        $city = $customer->city 
            ?? $billingAddress['city'] 
            ?? 'Jakarta';

        $countryCode = $customer->country_code 
            ?? $billingAddress['country_code'] 
            ?? 'ID';

        $postalCode = $customer->postal_code 
            ?? $billingAddress['postal_code'] 
            ?? '00000';

        $voice = $customer->phone 
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
            'name' => $customer->name ?? $user->name,
            'email' => $customer->email ?? $user->email,
            'password' => 'TempPassword123!', // RDASH requires password, akan di-update nanti
            'password_confirmation' => 'TempPassword123!',
            'organization' => $organization,
            'street_1' => $street1,
            'street_2' => $customer->street_2 ?? $billingAddress['street_2'] ?? null,
            'city' => $city,
            'state' => $customer->state ?? $billingAddress['state'] ?? null,
            'country_code' => $countryCode,
            'postal_code' => $postalCode,
            'voice' => $voice,
            'fax' => $customer->fax ?? $billingAddress['fax'] ?? null,
        ];
    }
}

