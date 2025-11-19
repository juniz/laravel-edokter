<?php

namespace App\Domain\Rdash\BareMetal\ValueObjects;

readonly class BareMetalOrder
{
    public function __construct(
        public int $id,
        public int $bareMetalProductId,
        public int $customerId,
        public string $name, // FQDN
        public string $cycle, // monthly, quarterly, annually
        public string $os,
        public string $status,
        public string $state, // on, off
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            bareMetalProductId: $data['bare_metal_product_id'] ?? $data['bareMetalProductId'] ?? 0,
            customerId: $data['customer_id'] ?? $data['customerId'] ?? 0,
            name: $data['name'] ?? '',
            cycle: $data['cycle'] ?? '',
            os: $data['os'] ?? '',
            status: $data['status'] ?? '',
            state: $data['state'] ?? 'off',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bare_metal_product_id' => $this->bareMetalProductId,
            'customer_id' => $this->customerId,
            'name' => $this->name,
            'cycle' => $this->cycle,
            'os' => $this->os,
            'status' => $this->status,
            'state' => $this->state,
            ...$this->metadata,
        ];
    }
}

