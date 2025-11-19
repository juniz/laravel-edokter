<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository as RdashDomainRepositoryContract;
use App\Domain\Rdash\Domain\ValueObjects\DomainAvailability;
use App\Domain\Rdash\Domain\ValueObjects\DomainWhois;
use App\Domain\Rdash\Domain\ValueObjects\RdashDomain;
use App\Infrastructure\Rdash\HttpClient;

class RdashDomainRepository implements RdashDomainRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {
    }

    public function getAll(array $filters = []): array
    {
        $response = $this->client->get('/domains', $filters);
        
        // Handle RDASH API response structure: {success: true, data: [...], message: "Success"}
        $domains = $response['data'] ?? [];

        return array_map(
            fn (array $domain) => RdashDomain::fromArray($domain),
            $domains
        );
    }

    public function getById(int $domainId): ?RdashDomain
    {
        $response = $this->client->get("/domains/{$domainId}");

        // Handle RDASH API response structure: {success: true, data: {...}, message: "Success"}
        $data = $response['data'] ?? $response;

        if (empty($data) || !isset($data['id'])) {
            return null;
        }

        return RdashDomain::fromArray($data);
    }

    public function getByName(string $domainName): ?RdashDomain
    {
        try {
            $response = $this->client->get('/domains/details', ['domain_name' => $domainName]);

            // Handle RDASH API response structure: {success: true, data: {...}, message: "Success"}
            $data = $response['data'] ?? $response;

            if (empty($data) || !isset($data['id'])) {
                return null;
            }

            return RdashDomain::fromArray($data);
        } catch (\RuntimeException $e) {
            // Handle 404 Not Found response
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'not found')) {
                return null;
            }
            throw $e;
        }
    }

    public function checkAvailability(string $domain, bool $includePremium = false): DomainAvailability
    {
        $response = $this->client->get('/domains/availability', [
            'domain' => $domain,
            'include_premium_domains' => $includePremium,
        ]);

        // Handle RDASH API response structure: {success: true, data: [{name, available, message}], message: "Success"}
        $data = $response['data'] ?? [];

        // DomainAvailability expects array of items or single item
        return DomainAvailability::fromArray($data);
    }

    public function getWhois(string $domain): DomainWhois
    {
        $data = $this->client->get('/domains/whois', ['domain' => $domain]);

        return DomainWhois::fromArray($data);
    }

    public function register(array $data): RdashDomain
    {
        $response = $this->client->post('/domains', $data);

        // Handle RDASH API response structure: {success: true, data: {...}, message: "Success"}
        $domainData = $response['data'] ?? $response;

        return RdashDomain::fromArray($domainData);
    }

    public function transfer(array $data): RdashDomain
    {
        $response = $this->client->post('/domains/transfer', $data);

        return RdashDomain::fromArray($response);
    }

    public function renew(int $domainId, array $data): RdashDomain
    {
        $response = $this->client->post("/domains/{$domainId}/renew", $data);

        return RdashDomain::fromArray($response);
    }

    public function updateNameservers(int $domainId, array $nameservers, ?int $customerId = null): RdashDomain
    {
        $data = [];
        foreach ($nameservers as $index => $nameserver) {
            $data["nameserver[{$index}]"] = $nameserver;
        }
        if ($customerId) {
            $data['customer_id'] = $customerId;
        }

        $response = $this->client->put("/domains/{$domainId}/ns", $data);

        return RdashDomain::fromArray($response);
    }

    public function updateContacts(int $domainId, array $contacts): RdashDomain
    {
        $response = $this->client->put("/domains/{$domainId}/contacts", $contacts);

        return RdashDomain::fromArray($response);
    }

    public function getAuthCode(int $domainId): string
    {
        $data = $this->client->get("/domains/{$domainId}/auth_code");

        return $data['auth_code'] ?? '';
    }

    public function resetAuthCode(int $domainId, string $authCode): bool
    {
        $this->client->put("/domains/{$domainId}/auth_code", ['auth_code' => $authCode]);

        return true;
    }

    public function lock(int $domainId, ?string $reason = null): bool
    {
        $data = [];
        if ($reason) {
            $data['reason'] = $reason;
        }
        $this->client->put("/domains/{$domainId}/locked", $data);

        return true;
    }

    public function unlock(int $domainId): bool
    {
        $this->client->delete("/domains/{$domainId}/locked");

        return true;
    }

    public function registrarLock(int $domainId, ?string $reason = null): bool
    {
        $data = [];
        if ($reason) {
            $data['reason'] = $reason;
        }
        $this->client->put("/domains/{$domainId}/registrar-locked", $data);

        return true;
    }

    public function registrarUnlock(int $domainId): bool
    {
        $this->client->delete("/domains/{$domainId}/registrar-locked");

        return true;
    }

    public function suspend(int $domainId, int $type, string $reason): bool
    {
        $this->client->put("/domains/{$domainId}/suspended", [
            'type' => $type,
            'reason' => $reason,
        ]);

        return true;
    }

    public function unsuspend(int $domainId): bool
    {
        $this->client->delete("/domains/{$domainId}/suspended");

        return true;
    }

    public function move(int $domainId, int $newCustomerId): bool
    {
        $this->client->post("/domains/{$domainId}/move", [
            'customer_id' => $newCustomerId,
        ]);

        return true;
    }

    public function restore(int $domainId): bool
    {
        $this->client->post("/domains/{$domainId}/restore");

        return true;
    }

    public function cancelTransfer(int $domainId, array $data): bool
    {
        $this->client->post("/domains/{$domainId}/transfer/cancel", $data);

        return true;
    }

    public function resendVerification(int $domainId): bool
    {
        $this->client->post("/domains/{$domainId}/verification/resend");

        return true;
    }

    public function getDocumentUploadLink(int $domainId): string
    {
        $data = $this->client->post("/domains/{$domainId}/documents/link");

        return $data['link'] ?? '';
    }

    public function delete(int $domainId): bool
    {
        $this->client->delete("/domains/{$domainId}");

        return true;
    }
}

