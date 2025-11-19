<?php

namespace App\Domain\Rdash\ObjectStorage\ValueObjects;

readonly class ObjectStorage
{
    public function __construct(
        public int $id,
        public string $name,
        public int $customerId,
        public int $size, // in GB
        public int $billingCycle, // in months (1-36)
        public string $status,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            customerId: $data['customer_id'] ?? $data['customerId'] ?? 0,
            size: $data['size'] ?? 0,
            billingCycle: $data['billing_cycle'] ?? $data['billingCycle'] ?? 0,
            status: $data['status'] ?? '',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'customer_id' => $this->customerId,
            'size' => $this->size,
            'billing_cycle' => $this->billingCycle,
            'status' => $this->status,
            ...$this->metadata,
        ];
    }
}

