<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Exceptions\InvalidTicketStatusTransitionException;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketWorkflowService
{
    public function nextStatus(TicketStatus $status): ?TicketStatus
    {
        return match ($status) {
            TicketStatus::OPEN => TicketStatus::IN_PROGRESS,
            TicketStatus::IN_PROGRESS => TicketStatus::DONE,
            TicketStatus::DONE => null,
        };
    }

    public function changeStatus(Ticket $ticket, TicketStatus $status): Ticket
    {
        return DB::transaction(function () use ($ticket, $status): Ticket {
            $lockedTicket = Ticket::query()
                ->whereKey($ticket->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->nextStatus($lockedTicket->status) !== $status) {
                throw new InvalidTicketStatusTransitionException(
                    $lockedTicket->status,
                    $status,
                );
            }

            $lockedTicket->update(['status' => $status]);

            return $lockedTicket->refresh();
        });
    }
}
