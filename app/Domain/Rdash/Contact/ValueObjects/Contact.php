<?php

namespace App\Domain\Rdash\Contact\ValueObjects;

use App\Domain\Rdash\Customer\ValueObjects\Address;

readonly class Contact
{
    public function __construct(
        public int $id,
        public int $customerId,
        public string $label, // Default, Admin, Technical, Billing, Registrant
        public string $name,
        public string $email,
        public string $organization,
        public Address $address,
        public ?string $phone = null,
        public ?string $fax = null,
        public ?string $reference = null, // Domain name reference
        public array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            customerId: $data['customer_id'] ?? 0,
            label: $data['label'] ?? 'Default',
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            organization: $data['organization'] ?? '',
            address: Address::fromArray($data),
            phone: $data['voice'] ?? $data['phone'] ?? null,
            fax: $data['fax'] ?? null,
            reference: $data['reference'] ?? null,
            metadata: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'label' => $this->label,
            'name' => $this->name,
            'email' => $this->email,
            'organization' => $this->organization,
            'street_1' => $this->address->street1,
            'street_2' => $this->address->street2,
            'city' => $this->address->city,
            'state' => $this->address->state,
            'country_code' => $this->address->countryCode,
            'postal_code' => $this->address->postalCode,
            'voice' => $this->phone,
            'fax' => $this->fax,
            'reference' => $this->reference,
            ...$this->metadata,
        ];
    }
}

