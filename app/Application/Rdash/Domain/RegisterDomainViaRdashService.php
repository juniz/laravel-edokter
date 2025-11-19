<?php

namespace App\Application\Rdash\Domain;

use App\Domain\Customer\Contracts\CustomerRepository;
use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;
use App\Domain\Rdash\Domain\ValueObjects\RdashDomain;
use App\Models\Domain\Customer\Domain;
use Illuminate\Support\Facades\Log;

class RegisterDomainViaRdashService
{
    public function __construct(
        private RdashDomainRepository $rdashDomainRepository,
        private CustomerRepository $customerRepository
    ) {
    }

    /**
     * Register domain via RDASH dan simpan ke local database
     *
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string, domain?: Domain, rdash_domain?: RdashDomain}
     */
    public function execute(array $data): array
    {
        try {
            // Validasi customer_id
            $customer = $this->customerRepository->findByUlid($data['customer_id']);
            if (!$customer) {
                return [
                    'success' => false,
                    'message' => 'Customer tidak ditemukan.',
                ];
            }

            // Pastikan customer sudah sync ke RDASH
            if (!$customer->rdash_customer_id) {
                return [
                    'success' => false,
                    'message' => 'Customer belum di-sync ke RDASH. Silakan sync customer terlebih dahulu.',
                ];
            }

            // Set customer_id ke RDASH customer ID
            $data['customer_id'] = $customer->rdash_customer_id;

            // Register domain di RDASH
            $rdashDomain = $this->rdashDomainRepository->register($data);

            // Simpan ke local database
            $domain = Domain::create([
                'customer_id' => $customer->id,
                'name' => $rdashDomain->name,
                'status' => $this->mapRdashStatusToLocal($rdashDomain->status),
                'auto_renew' => $data['auto_renew'] ?? false,
                'rdash_domain_id' => $rdashDomain->id,
                'rdash_synced_at' => now(),
                'rdash_sync_status' => 'synced',
                'rdash_verification_status' => $rdashDomain->verificationStatus,
                'rdash_required_document' => $rdashDomain->requiredDocument,
            ]);

            return [
                'success' => true,
                'message' => 'Domain berhasil di-register di RDASH.',
                'domain' => $domain,
                'rdash_domain' => $rdashDomain,
            ];
        } catch (\Exception $e) {
            Log::error('Register Domain via RDASH failed', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal register domain: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Map RDASH status ke local status
     */
    private function mapRdashStatusToLocal(int $rdashStatus): string
    {
        return match ($rdashStatus) {
            1 => 'active',
            2 => 'expired',
            default => 'pending',
        };
    }
}

