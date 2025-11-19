<?php

namespace App\Domain\Rdash\Domain\ValueObjects;

readonly class RdashDomain
{
    public function __construct(
        public int $id,
        public string $name,
        public int $customerId,
        public int $status, // 0. Pending, 1. Active, 2. Expired, 3. Pending Delete, 4. Deleted, 5. Pending Transfer, 6. Transferred Away, 7. Suspended, 8. Rejected
        public int $verificationStatus, // 0. Waiting, 1. Verifying, 2. Document Validating, 3. Active
        public bool $requiredDocument,
        public ?string $expiredAt = null,
        public ?string $createdAt = null,
        public array $nameservers = [],
        public array $contacts = [],
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            customerId: $data['customer_id'] ?? 0,
            status: $data['status'] ?? 0,
            verificationStatus: $data['verification_status'] ?? $data['verificationStatus'] ?? 0,
            requiredDocument: ($data['required_document'] ?? 0) === 1,
            expiredAt: $data['expired_at'] ?? $data['expiredAt'] ?? null,
            createdAt: $data['created_at'] ?? $data['createdAt'] ?? null,
            nameservers: $data['nameservers'] ?? [],
            contacts: $data['contacts'] ?? [],
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'customer_id' => $this->customerId,
            'status' => $this->status,
            'verification_status' => $this->verificationStatus,
            'required_document' => $this->requiredDocument ? 1 : 0,
            'expired_at' => $this->expiredAt,
            'created_at' => $this->createdAt,
            'nameservers' => $this->nameservers,
            'contacts' => $this->contacts,
            ...$this->metadata,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function isExpired(): bool
    {
        return $this->status === 2;
    }

    public function isSuspended(): bool
    {
        return $this->status === 7;
    }
}

