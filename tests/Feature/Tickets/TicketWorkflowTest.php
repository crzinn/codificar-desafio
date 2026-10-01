<?php

namespace Tests\Feature\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_moves_an_open_ticket_to_in_progress(): void
    {
        $ticket = Ticket::factory()->open()->create();

        $this->from(route('tickets.index'))
            ->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ])
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame(TicketStatus::IN_PROGRESS, $ticket->refresh()->status);
    }

    public function test_it_moves_an_in_progress_ticket_to_done(): void
    {
        $ticket = Ticket::factory()->inProgress()->create();

        $this->patch(route('tickets.status.update', $ticket), [
            'status' => TicketStatus::DONE->value,
        ])->assertSessionHasNoErrors();

        $this->assertSame(TicketStatus::DONE, $ticket->refresh()->status);
    }

    public function test_it_rejects_skipped_or_backwards_transitions(): void
    {
        $openTicket = Ticket::factory()->open()->create();
        $doneTicket = Ticket::factory()->done()->create();

        $this->from(route('tickets.index'))
            ->patch(route('tickets.status.update', $openTicket), [
                'status' => TicketStatus::DONE->value,
            ])
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHasErrors('status');

        $this->from(route('tickets.index'))
            ->patch(route('tickets.status.update', $doneTicket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ])
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHasErrors('status');

        $this->assertSame(TicketStatus::OPEN, $openTicket->refresh()->status);
        $this->assertSame(TicketStatus::DONE, $doneTicket->refresh()->status);
    }

    public function test_the_board_receives_only_the_next_allowed_status(): void
    {
        $openTicket = Ticket::factory()->open()->create([
            'opened_at' => now()->subMinutes(2),
        ]);
        $inProgressTicket = Ticket::factory()->inProgress()->create([
            'opened_at' => now()->subMinute(),
        ]);
        $doneTicket = Ticket::factory()->done()->create([
            'opened_at' => now(),
        ]);

        $this->get(route('tickets.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tickets/Index')
                ->where('tickets.0.id', $doneTicket->id)
                ->where('tickets.0.next_status', null)
                ->where('tickets.1.id', $inProgressTicket->id)
                ->where('tickets.1.next_status', TicketStatus::DONE->value)
                ->where('tickets.2.id', $openTicket->id)
                ->where('tickets.2.next_status', TicketStatus::IN_PROGRESS->value),
            );
    }
}
