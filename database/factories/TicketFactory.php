<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Responsible;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ticket> */
class TicketFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'requester_name' => fake()->name(),
            'priority' => fake()->randomElement(TicketPriority::cases()),
            'status' => TicketStatus::OPEN,
            'responsible_id' => Responsible::factory(),
            'opened_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }

    public function lowPriority(): static
    {
        return $this->state(fn (): array => ['priority' => TicketPriority::LOW]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn (): array => ['priority' => TicketPriority::MEDIUM]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (): array => ['priority' => TicketPriority::HIGH]);
    }

    public function open(): static
    {
        return $this->state(fn (): array => ['status' => TicketStatus::OPEN]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => ['status' => TicketStatus::IN_PROGRESS]);
    }

    public function done(): static
    {
        return $this->state(fn (): array => ['status' => TicketStatus::DONE]);
    }
}
