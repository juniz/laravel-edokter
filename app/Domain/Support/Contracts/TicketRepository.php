<?php

namespace App\Domain\Support\Contracts;

use App\Models\Domain\Support\Ticket;

interface TicketRepository
{
    public function create(array $data): Ticket;
    public function findByUlid(string $id): ?Ticket;
    public function findByCustomer(string $customerId): array;
    public function updateStatus(Ticket $ticket, string $status): void;
}

