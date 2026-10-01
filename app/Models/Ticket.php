<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $requester_name
 * @property TicketPriority $priority
 * @property TicketStatus $status
 * @property int $responsible_id
 * @property Carbon $opened_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Responsible $responsible
 */
#[Fillable([
    'title',
    'description',
    'requester_name',
    'priority',
    'status',
    'responsible_id',
    'opened_at',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    /** @return BelongsTo<Responsible, $this> */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Responsible::class);
    }

    /** @return array<string, string|class-string> */
    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'opened_at' => 'datetime',
        ];
    }
}
