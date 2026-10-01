<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Exceptions\InvalidTicketStatusTransitionException;
use App\Exceptions\NoResponsibleAvailableException;
use App\Http\Requests\IndexTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Responsible;
use App\Models\Ticket;
use App\Services\TicketService;
use App\Services\TicketWorkflowService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly TicketWorkflowService $ticketWorkflowService,
    ) {}

    public function index(IndexTicketRequest $request): Response
    {
        $filters = $request->validated();
        $query = Ticket::query()->with('responsible:id,name');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['responsible_id'])) {
            $query->where('responsible_id', $filters['responsible_id']);
        }

        $tickets = $query
            ->orderByDesc('opened_at')
            ->get()
            ->map(fn (Ticket $ticket): array => [
                ...$ticket->toArray(),
                'next_status' => $this->ticketWorkflowService
                    ->nextStatus($ticket->status)?->value,
            ]);

        return Inertia::render('tickets/Index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'responsibles' => $this->responsibles(),
            'priorities' => $this->priorityOptions(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('tickets/Create', [
            'responsibles' => $this->activeResponsibles(),
            'priorities' => $this->priorityOptions(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        try {
            $ticket = $this->ticketService->create($request->validated());
        } catch (NoResponsibleAvailableException $exception) {
            throw ValidationException::withMessages([
                'assignment_mode' => $exception->getMessage(),
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Chamado criado com sucesso.',
        ]);

        return to_route('tickets.show', $ticket);
    }

    public function show(Ticket $ticket): Response
    {
        return Inertia::render('tickets/Show', [
            'ticket' => $ticket->load('responsible:id,name'),
            'priorities' => $this->priorityOptions(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        return Inertia::render('tickets/Edit', [
            'ticket' => $ticket->load('responsible:id,name'),
            'responsibles' => $this->activeResponsibles(),
            'priorities' => $this->priorityOptions(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket = $this->ticketService->update($ticket, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Chamado atualizado com sucesso.',
        ]);

        return to_route('tickets.show', $ticket);
    }

    public function updateStatus(
        UpdateTicketStatusRequest $request,
        Ticket $ticket,
    ): RedirectResponse {
        $status = TicketStatus::from((string) $request->validated('status'));

        try {
            $this->ticketWorkflowService->changeStatus($ticket, $status);
        } catch (InvalidTicketStatusTransitionException $exception) {
            throw ValidationException::withMessages([
                'status' => $exception->getMessage(),
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Status do chamado atualizado.',
        ]);

        return back();
    }

    /** @return Collection<int, Responsible> */
    private function responsibles(): Collection
    {
        return Responsible::query()->orderBy('name')->get(['id', 'name', 'active']);
    }

    /** @return Collection<int, Responsible> */
    private function activeResponsibles(): Collection
    {
        return Responsible::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** @return list<array{value: string, label: string}> */
    private function priorityOptions(): array
    {
        return array_map(
            fn (TicketPriority $priority): array => [
                'value' => $priority->value,
                'label' => $priority->label(),
            ],
            TicketPriority::cases(),
        );
    }

    /** @return list<array{value: string, label: string, board_label: string}> */
    private function statusOptions(): array
    {
        return array_map(
            fn (TicketStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'board_label' => $status->boardLabel(),
            ],
            TicketStatus::cases(),
        );
    }
}
