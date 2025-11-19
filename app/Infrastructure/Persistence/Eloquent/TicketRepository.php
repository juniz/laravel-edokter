<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Support\Contracts\TicketRepository as TicketRepositoryContract;
use App\Models\Domain\Support\Ticket;

class TicketRepository implements TicketRepositoryContract
{
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function findByUlid(string $id): ?Ticket
    {
        return Ticket::find($id);
    }

    public function findByCustomer(string $customerId): array
    {
        return Ticket::where('customer_id', $customerId)->get()->all();
    }

    public function updateStatus(Ticket $ticket, string $status): void
    {
        $ticket->update(['status' => $status]);
    }
}

