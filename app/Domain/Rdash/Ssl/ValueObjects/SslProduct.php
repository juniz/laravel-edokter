<?php

namespace App\Domain\Rdash\Ssl\ValueObjects;

readonly class SslProduct
{
    public function __construct(
        public int $id,
        public string $provider,
        public string $brand,
        public string $name,
        public string $sslType,
        public bool $isWildcard,
        public bool $isRefundable,
        public int $maxPeriod,
        public int $status,
        public array $features,
        public ?int $price = null,
        public string $currency = 'IDR',
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            provider: $data['provider'] ?? '',
            brand: $data['brand'] ?? '',
            name: $data['name'] ?? '',
            sslType: $data['ssl_type'] ?? 'DV',
            isWildcard: ($data['is_wildcard'] ?? 0) === 1,
            isRefundable: ($data['is_refundable'] ?? 0) === 1,
            maxPeriod: $data['max_period'] ?? 1,
            status: $data['status'] ?? 0,
            features: $data['features'] ?? [],
            price: $data['price'] ?? null,
            currency: $data['currency'] ?? 'IDR',
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'brand' => $this->brand,
            'name' => $this->name,
            'ssl_type' => $this->sslType,
            'is_wildcard' => $this->isWildcard ? 1 : 0,
            'is_refundable' => $this->isRefundable ? 1 : 0,
            'max_period' => $this->maxPeriod,
            'status' => $this->status,
            'features' => $this->features,
            'price' => $this->price,
            'currency' => $this->currency,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            ...$this->metadata,
        ];
    }
}
