<?php

namespace App\Domain\Rdash\Contact\Contracts;

use App\Domain\Rdash\Contact\ValueObjects\Contact;

interface ContactRepository
{
    /**
     * Get all contacts by customer id
     *
     * @param array<string, mixed> $filters
     * @return array<int, Contact>
     */
    public function getByCustomerId(int $customerId, array $filters = []): array;

    /**
     * Get contact by customer id and contact id
     */
    public function getById(int $customerId, int $contactId): ?Contact;

    /**
     * Create new contact
     *
     * @param array<string, mixed> $data
     */
    public function create(int $customerId, array $data): Contact;

    /**
     * Update contact by id
     *
     * @param array<string, mixed> $data
     */
    public function update(int $customerId, int $contactId, array $data): Contact;

    /**
     * Delete contact by id
     */
    public function delete(int $customerId, int $contactId): bool;
}

