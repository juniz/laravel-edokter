<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\Contact\Contracts\ContactRepository as ContactRepositoryContract;
use App\Domain\Rdash\Contact\ValueObjects\Contact;
use App\Infrastructure\Rdash\HttpClient;

class ContactRepository implements ContactRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {
    }

    public function getByCustomerId(int $customerId, array $filters = []): array
    {
        $data = $this->client->get("/customers/{$customerId}/contacts", $filters);
        $contacts = $data['data'] ?? [];

        return array_map(
            fn (array $contact) => Contact::fromArray(array_merge($contact, ['customer_id' => $customerId])),
            $contacts
        );
    }

    public function getById(int $customerId, int $contactId): ?Contact
    {
        $data = $this->client->get("/customers/{$customerId}/contacts/{$contactId}");

        return empty($data) ? null : Contact::fromArray(array_merge($data, ['customer_id' => $customerId]));
    }

    public function create(int $customerId, array $data): Contact
    {
        $response = $this->client->post("/customers/{$customerId}/contacts", $data);

        return Contact::fromArray(array_merge($response, ['customer_id' => $customerId]));
    }

    public function update(int $customerId, int $contactId, array $data): Contact
    {
        $response = $this->client->put("/customers/{$customerId}/contacts/{$contactId}", $data);

        return Contact::fromArray(array_merge($response, ['customer_id' => $customerId]));
    }

    public function delete(int $customerId, int $contactId): bool
    {
        $this->client->delete("/customers/{$customerId}/contacts/{$contactId}");

        return true;
    }
}

