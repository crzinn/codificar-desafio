<?php

namespace Tests\Unit\Services;

use App\Exceptions\NoResponsibleAvailableException;
use App\Models\Responsible;
use App\Models\Ticket;
use App\Services\TicketAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TicketAssignmentService::class);
    }

    public function test_it_selects_the_responsible_with_fewer_open_tickets(): void
    {
        $leastBusy = Responsible::factory()->create();
        $busier = Responsible::factory()->create();

        Ticket::factory()->for($leastBusy)->highPriority()->open()->create();
        Ticket::factory()->for($busier)->lowPriority()->open()->count(2)->create();
        Ticket::factory()->for($busier)->done()->count(3)->create();

        $this->assertTrue($this->service->findBestResponsible()->is($leastBusy));
    }

    public function test_it_uses_weighted_workload_to_resolve_a_count_tie(): void
    {
        $heavier = Responsible::factory()->create();
        $lighter = Responsible::factory()->create();

        Ticket::factory()->for($heavier)->highPriority()->open()->create();
        Ticket::factory()->for($lighter)->lowPriority()->inProgress()->create();

        $this->assertTrue($this->service->findBestResponsible()->is($lighter));
    }

    public function test_it_uses_the_oldest_last_assignment_after_load_ties(): void
    {
        $mostRecent = Responsible::factory()->create([
            'last_assigned_at' => now()->subHour(),
        ]);
        $oldest = Responsible::factory()->create([
            'last_assigned_at' => now()->subDay(),
        ]);

        $this->assertTrue($this->service->findBestResponsible()->is($oldest));
    }

    public function test_it_prioritizes_someone_never_assigned_in_a_load_tie(): void
    {
        Responsible::factory()->create(['last_assigned_at' => now()->subDay()]);
        $neverAssigned = Responsible::factory()->create(['last_assigned_at' => null]);

        $this->assertTrue($this->service->findBestResponsible()->is($neverAssigned));
    }

    public function test_it_uses_the_lowest_id_as_the_final_tie_breaker(): void
    {
        $first = Responsible::factory()->create();
        Responsible::factory()->create();

        $this->assertTrue($this->service->findBestResponsible()->is($first));
    }

    public function test_it_ignores_inactive_responsibles(): void
    {
        Responsible::factory()->inactive()->create();
        $active = Responsible::factory()->create();
        Ticket::factory()->for($active)->highPriority()->open()->count(2)->create();

        $this->assertTrue($this->service->findBestResponsible()->is($active));
    }

    public function test_it_throws_when_no_active_responsible_is_available(): void
    {
        Responsible::factory()->inactive()->create();

        $this->expectException(NoResponsibleAvailableException::class);

        $this->service->findBestResponsible();
    }
}
