<?php

namespace Tests\Feature\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_casts_domain_values_and_relates_to_a_responsible(): void
    {
        $responsible = Responsible::factory()->create();
        $ticket = Ticket::factory()->for($responsible)->create([
            'priority' => TicketPriority::HIGH,
            'status' => TicketStatus::IN_PROGRESS,
            'opened_at' => '2026-09-26 10:00:00',
        ]);

        $ticket->refresh();

        $this->assertSame(TicketPriority::HIGH, $ticket->priority);
        $this->assertSame(TicketStatus::IN_PROGRESS, $ticket->status);
        $this->assertTrue($ticket->opened_at->isSameMinute('2026-09-26 10:00:00'));
        $this->assertTrue($ticket->responsible->is($responsible));
        $this->assertTrue($responsible->tickets()->firstOrFail()->is($ticket));
    }

    public function test_it_casts_responsible_availability_and_last_assignment(): void
    {
        $responsible = Responsible::factory()->inactive()->create([
            'last_assigned_at' => '2026-09-26 09:00:00',
        ]);

        $responsible->refresh();

        $this->assertFalse($responsible->active);
        $this->assertTrue(
            $responsible->last_assigned_at?->isSameMinute('2026-09-26 09:00:00'),
        );
    }
}
