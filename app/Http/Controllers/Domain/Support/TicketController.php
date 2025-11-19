<?php

namespace App\Http\Controllers\Domain\Support;

use App\Http\Controllers\Controller;
use App\Domain\Support\Contracts\TicketRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    public function index(Request $request): Response
    {
        // Check if this is admin route
        if ($request->routeIs('admin.tickets.index')) {
            $query = \App\Models\Domain\Support\Ticket::with(['customer']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by priority
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $tickets = $query->latest()->paginate(15);

            return Inertia::render('admin/tickets/Index', [
                'tickets' => $tickets,
                'filters' => $request->only('status', 'priority'),
            ]);
        }

        // Customer route
        $customer = $request->user()->customer;
        
        if (!$customer) {
            return Inertia::render('tickets/Index', [
                'tickets' => [],
            ]);
        }

        $tickets = $this->ticketRepository->findByCustomer($customer->id);

        return Inertia::render('tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('tickets/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'message' => ['required', 'string'],
        ]);

        $customer = $request->user()->customer;
        
        if (!$customer) {
            return redirect()->back()->with('error', 'Customer profile tidak ditemukan.');
        }

        $ticket = $this->ticketRepository->create([
            'customer_id' => $customer->id,
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        // Create first reply
        $ticket->replies()->create([
            'customer_id' => $customer->id,
            'message' => $validated['message'],
        ]);

        return redirect()->route('customer.tickets.show', $ticket->id)
            ->with('success', 'Ticket berhasil dibuat.');
    }

    public function show(Request $request, string $id): Response
    {
        $ticket = $this->ticketRepository->findByUlid($id);

        if (!$ticket) {
            abort(404);
        }

        // Check if this is admin route
        if ($request->routeIs('admin.tickets.show')) {
            return Inertia::render('admin/tickets/Show', [
                'ticket' => $ticket->load(['replies.user', 'replies.customer', 'customer']),
            ]);
        }

        return Inertia::render('tickets/Show', [
            'ticket' => $ticket->load(['replies.user', 'replies.customer']),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $ticket = $this->ticketRepository->findByUlid($id);

        if (!$ticket) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'in:open,pending,solved,closed'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
        ]);

        $ticket->update($validated);

        return redirect()->back()->with('success', 'Ticket berhasil diperbarui.');
    }

    public function assign(Request $request, string $id)
    {
        $ticket = $this->ticketRepository->findByUlid($id);

        if (!$ticket) {
            abort(404);
        }

        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        // For now, we'll just update status if assigned
        if ($validated['user_id']) {
            $ticket->update(['status' => 'pending']);
        }

        return redirect()->back()->with('success', 'Ticket berhasil di-assign.');
    }

    public function reply(Request $request, string $id)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10'],
        ]);

        $ticket = $this->ticketRepository->findByUlid($id);

        if (!$ticket) {
            abort(404);
        }

        // Check if this is admin route (agent reply)
        if ($request->routeIs('admin.tickets.reply')) {
            $ticket->replies()->create([
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
            ]);

            // Update ticket status to pending if it was solved/closed
            if (in_array($ticket->status, ['solved', 'closed'])) {
                $ticket->update(['status' => 'pending']);
            }

            return redirect()->back()->with('success', 'Reply berhasil dikirim.');
        }

        // Customer reply
        $customer = $request->user()->customer;
        
        if (!$customer) {
            return redirect()->back()->with('error', 'Customer profile tidak ditemukan.');
        }

        $ticket->replies()->create([
            'customer_id' => $customer->id,
            'message' => $validated['message'],
        ]);

        return redirect()->back()->with('success', 'Reply berhasil dikirim.');
    }
}
