<?php

namespace Tests\Feature\Tickets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TicketCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_tickets_with_filters(): void
    {
        $responsible = Responsible::factory()->create();
        $matchingTicket = Ticket::factory()->for($responsible)->highPriority()->open()->create();
        Ticket::factory()->for($responsible)->lowPriority()->done()->create();

        $this->get(route('tickets.index', [
            'status' => TicketStatus::OPEN->value,
            'priority' => TicketPriority::HIGH->value,
            'responsible_id' => $responsible->id,
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tickets/Index')
                ->has('tickets', 1)
                ->where('tickets.0.id', $matchingTicket->id)
                ->where('filters.status', TicketStatus::OPEN->value),
            );
    }

    public function test_it_renders_the_create_form(): void
    {
        Responsible::factory()->count(3)->create();

        $this->get(route('tickets.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tickets/Create')
                ->has('responsibles', 3)
                ->has('priorities', 3),
            );
    }

    public function test_it_creates_a_manually_assigned_ticket(): void
    {
        $responsible = Responsible::factory()->create();

        $response = $this->post(route('tickets.store'), [
            'title' => 'Notebook sem acesso à rede',
            'description' => 'O notebook não conecta na rede interna.',
            'requester_name' => 'Marina Lopes',
            'priority' => TicketPriority::HIGH->value,
            'assignment_mode' => 'manual',
            'responsible_id' => $responsible->id,
        ]);

        $ticket = Ticket::query()->sole();

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame(TicketStatus::OPEN, $ticket->status);
        $this->assertSame($responsible->id, $ticket->responsible_id);
        $this->assertNotNull($ticket->opened_at);
        $this->assertNotNull($responsible->refresh()->last_assigned_at);
    }

    public function test_it_rejects_invalid_ticket_data_and_inactive_responsibles(): void
    {
        $inactiveResponsible = Responsible::factory()->inactive()->create();

        $this->post(route('tickets.store'), [
            'title' => '',
            'description' => '',
            'requester_name' => '',
            'priority' => 'URGENT',
            'assignment_mode' => 'manual',
            'responsible_id' => $inactiveResponsible->id,
        ])->assertSessionHasErrors([
            'title',
            'description',
            'requester_name',
            'priority',
            'responsible_id',
        ]);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_it_shows_a_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tickets/Show')
                ->where('ticket.id', $ticket->id)
                ->where('ticket.title', $ticket->title)
                ->has('ticket.responsible'),
            );
    }

    public function test_it_updates_a_ticket_and_its_manual_assignment(): void
    {
        $oldResponsible = Responsible::factory()->create();
        $newResponsible = Responsible::factory()->create();
        $ticket = Ticket::factory()->for($oldResponsible)->create();

        $response = $this->put(route('tickets.update', $ticket), [
            'title' => 'Título atualizado',
            'description' => 'Descrição atualizada.',
            'requester_name' => 'Solicitante atualizado',
            'priority' => TicketPriority::LOW->value,
            'status' => TicketStatus::IN_PROGRESS->value,
            'responsible_id' => $newResponsible->id,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $ticket->refresh();

        $this->assertSame('Título atualizado', $ticket->title);
        $this->assertSame(TicketPriority::LOW, $ticket->priority);
        $this->assertSame(TicketStatus::OPEN, $ticket->status);
        $this->assertSame($newResponsible->id, $ticket->responsible_id);
        $this->assertNotNull($newResponsible->refresh()->last_assigned_at);
    }
}
