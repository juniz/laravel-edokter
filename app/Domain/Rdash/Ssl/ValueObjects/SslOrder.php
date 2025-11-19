<?php

namespace App\Domain\Rdash\Ssl\ValueObjects;

readonly class SslOrder
{
    public function __construct(
        public int $id,
        public int $sslProductId,
        public int $customerId,
        public string $domain,
        public string $dcvMethod, // dns, http, https, email
        public ?string $dcvEmail = null,
        public int $period, // in months
        public string $status,
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            sslProductId: $data['ssl_product_id'] ?? $data['sslProductId'] ?? 0,
            customerId: $data['customer_id'] ?? $data['customerId'] ?? 0,
            domain: $data['domain'] ?? '',
            dcvMethod: $data['dcv_method'] ?? $data['dcvMethod'] ?? '',
            dcvEmail: $data['dcv_email'] ?? $data['dcvEmail'] ?? null,
            period: $data['period'] ?? 0,
            status: $data['status'] ?? '',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ssl_product_id' => $this->sslProductId,
            'customer_id' => $this->customerId,
            'domain' => $this->domain,
            'dcv_method' => $this->dcvMethod,
            'dcv_email' => $this->dcvEmail,
            'period' => $this->period,
            'status' => $this->status,
            ...$this->metadata,
        ];
    }
}

