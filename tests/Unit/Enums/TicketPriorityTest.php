<?php

namespace Tests\Unit\Enums;

use App\Enums\TicketPriority;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TicketPriorityTest extends TestCase
{
    /** @return array<string, array{TicketPriority, int}> */
    public static function priorities(): array
    {
        return [
            'low' => [TicketPriority::LOW, 1],
            'medium' => [TicketPriority::MEDIUM, 2],
            'high' => [TicketPriority::HIGH, 3],
        ];
    }

    #[DataProvider('priorities')]
    public function test_it_maps_priority_to_weight(
        TicketPriority $priority,
        int $expectedWeight,
    ): void {
        $this->assertSame($expectedWeight, $priority->weight());
    }
}
