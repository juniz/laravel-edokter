<?php

namespace App\Domain\Rdash\Account\ValueObjects;

readonly class DomainPrice
{
    public function __construct(
        public int $id,
        public string $extension,
        public int $price,
        public int $renewPrice,
        public int $transferPrice,
        public string $currency = 'IDR',
        public bool $promo = false,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            extension: $data['extension'] ?? '',
            price: $data['price'] ?? 0,
            renewPrice: $data['renew_price'] ?? $data['renewPrice'] ?? 0,
            transferPrice: $data['transfer_price'] ?? $data['transferPrice'] ?? 0,
            currency: $data['currency'] ?? 'IDR',
            promo: $data['promo'] ?? false,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'extension' => $this->extension,
            'price' => $this->price,
            'renew_price' => $this->renewPrice,
            'transfer_price' => $this->transferPrice,
            'currency' => $this->currency,
            'promo' => $this->promo,
            ...$this->metadata,
        ];
    }
}

