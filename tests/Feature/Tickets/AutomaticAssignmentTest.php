<?php

namespace Tests\Feature\Tickets;

use App\Enums\TicketPriority;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomaticAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_ticket_with_automatic_assignment(): void
    {
        $leastBusy = Responsible::factory()->create();
        $busier = Responsible::factory()->create();
        Ticket::factory()->for($busier)->open()->create();

        $response = $this->post(route('tickets.store'), [
            'title' => 'Acesso ao sistema bloqueado',
            'description' => 'O solicitante não consegue acessar o sistema interno.',
            'requester_name' => 'Fernanda Melo',
            'priority' => TicketPriority::MEDIUM->value,
            'assignment_mode' => 'automatic',
        ]);

        $ticket = Ticket::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame($leastBusy->id, $ticket->responsible_id);
        $this->assertNotNull($leastBusy->refresh()->last_assigned_at);
    }

    public function test_it_does_not_partially_create_a_ticket_without_an_available_responsible(): void
    {
        Responsible::factory()->inactive()->create();

        $this->post(route('tickets.store'), [
            'title' => 'Acesso ao sistema bloqueado',
            'description' => 'O solicitante não consegue acessar o sistema interno.',
            'requester_name' => 'Fernanda Melo',
            'priority' => TicketPriority::MEDIUM->value,
            'assignment_mode' => 'automatic',
        ])->assertSessionHasErrors('assignment_mode');

        $this->assertDatabaseCount('tickets', 0);
    }
}
