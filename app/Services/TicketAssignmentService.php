<?php

namespace App\Services;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Exceptions\NoResponsibleAvailableException;
use App\Models\Responsible;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketAssignmentService
{
    public function findBestResponsible(): Responsible
    {
        return DB::transaction(function (): Responsible {
            $responsibles = Responsible::query()
                ->where('active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($responsibles->isEmpty()) {
                throw new NoResponsibleAvailableException;
            }

            $loads = $this->loadsFor($responsibles);

            return $responsibles
                ->sort(function (Responsible $left, Responsible $right) use ($loads): int {
                    $leftLoad = $loads->get($left->id, [0, 0]);
                    $rightLoad = $loads->get($right->id, [0, 0]);

                    return [$leftLoad[0], $leftLoad[1], $this->lastAssignedOrder($left), $left->id]
                        <=> [$rightLoad[0], $rightLoad[1], $this->lastAssignedOrder($right), $right->id];
                })
                ->firstOrFail();
        });
    }

    /**
     * @param  EloquentCollection<int, Responsible>  $responsibles
     * @return Collection<int, array{int, int}>
     */
    private function loadsFor(EloquentCollection $responsibles): Collection
    {
        return DB::table('tickets')
            ->select('responsible_id')
            ->selectRaw('COUNT(*) AS active_ticket_count')
            ->selectRaw(
                'SUM(CASE priority WHEN ? THEN ? WHEN ? THEN ? WHEN ? THEN ? ELSE 0 END) AS workload_points',
                [
                    TicketPriority::LOW->value,
                    TicketPriority::LOW->weight(),
                    TicketPriority::MEDIUM->value,
                    TicketPriority::MEDIUM->weight(),
                    TicketPriority::HIGH->value,
                    TicketPriority::HIGH->weight(),
                ],
            )
            ->whereIn('responsible_id', $responsibles->modelKeys())
            ->whereIn('status', TicketStatus::openValues())
            ->groupBy('responsible_id')
            ->get()
            ->mapWithKeys(fn (object $load): array => [
                (int) $load->responsible_id => [
                    (int) $load->active_ticket_count,
                    (int) $load->workload_points,
                ],
            ]);
    }

    private function lastAssignedOrder(Responsible $responsible): int
    {
        return $responsible->last_assigned_at?->getTimestamp() ?? PHP_INT_MIN;
    }
}
