<?php

namespace App\Domain\Rdash\Account\ValueObjects;

readonly class AccountBalance
{
    public function __construct(
        public int $balance,
        public string $currency = 'IDR'
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            balance: $data['balance'] ?? 0,
            currency: $data['currency'] ?? 'IDR'
        );
    }

    public function toArray(): array
    {
        return [
            'balance' => $this->balance,
            'currency' => $this->currency,
        ];
    }
}

