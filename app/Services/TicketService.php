<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        private readonly TicketAssignmentService $ticketAssignmentService,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(array $data): Ticket
    {
        return DB::transaction(function () use ($data): Ticket {
            $assignmentMode = (string) $data['assignment_mode'];
            unset($data['assignment_mode']);

            $responsible = $assignmentMode === 'automatic'
                ? $this->ticketAssignmentService->findBestResponsible()
                : $this->lockActiveResponsible((int) $data['responsible_id']);

            $ticket = Ticket::query()->create([
                ...$data,
                'responsible_id' => $responsible->id,
                'status' => TicketStatus::OPEN,
                'opened_at' => now(),
            ]);

            $responsible->update(['last_assigned_at' => now()]);

            return $ticket;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Ticket $ticket, array $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $data): Ticket {
            $responsibleId = (int) $data['responsible_id'];

            if ($ticket->responsible_id !== $responsibleId) {
                $responsible = $this->lockActiveResponsible($responsibleId);
                $responsible->update(['last_assigned_at' => now()]);
            }

            $ticket->update($data);

            return $ticket->refresh();
        });
    }

    private function lockActiveResponsible(int $responsibleId): Responsible
    {
        return Responsible::query()
            ->whereKey($responsibleId)
            ->where('active', true)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
