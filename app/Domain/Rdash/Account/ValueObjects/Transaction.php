<?php

namespace App\Domain\Rdash\Account\ValueObjects;

readonly class Transaction
{
    public function __construct(
        public int $id,
        public string $type, // deposit, domain, ssl, object-storage, note
        public int $amount,
        public string $currency = 'IDR',
        public string $description,
        public ?string $tld = null,
        public ?string $date = null,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            type: $data['type'] ?? '',
            amount: $data['amount'] ?? 0,
            currency: $data['currency'] ?? 'IDR',
            description: $data['description'] ?? '',
            tld: $data['tld'] ?? null,
            date: $data['date'] ?? null,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'tld' => $this->tld,
            'date' => $this->date,
            ...$this->metadata,
        ];
    }
}

