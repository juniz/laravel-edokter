<?php

namespace App\Domain\Rdash\BareMetal\ValueObjects;

readonly class BareMetalProduct
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $price = null,
        public string $currency = 'IDR',
        public array $specs = [],
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            price: $data['price'] ?? null,
            currency: $data['currency'] ?? 'IDR',
            specs: $data['specs'] ?? [],
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'currency' => $this->currency,
            'specs' => $this->specs,
            ...$this->metadata,
        ];
    }
}

