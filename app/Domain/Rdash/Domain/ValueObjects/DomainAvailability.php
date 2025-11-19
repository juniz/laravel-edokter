<?php

namespace App\Domain\Rdash\Domain\ValueObjects;

readonly class DomainAvailability
{
    public function __construct(
        public string $name,
        public bool $available,
        public string $message,
        public array $metadata = []
    ) {
    }

    /**
     * Create from RDASH API response array
     * Response structure: [{name: "example.com", available: 1, message: "available"}]
     */
    public static function fromArray(array $data): self
    {
        // Handle both single item and array of items
        if (isset($data['name'])) {
            // Single item
            return new self(
                name: $data['name'] ?? '',
                available: ($data['available'] ?? 0) === 1,
                message: $data['message'] ?? '',
                metadata: $data
            );
        }

        // If array of items, take the first one
        if (is_array($data) && !empty($data) && isset($data[0])) {
            $first = $data[0];
            return new self(
                name: $first['name'] ?? '',
                available: ($first['available'] ?? 0) === 1,
                message: $first['message'] ?? '',
                metadata: $first
            );
        }

        // Fallback
        return new self(
            name: '',
            available: false,
            message: '',
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'available' => $this->available ? 1 : 0,
            'message' => $this->message,
            ...$this->metadata,
        ];
    }
}

