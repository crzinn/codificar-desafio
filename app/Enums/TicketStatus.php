<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'OPEN';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Aberto',
            self::IN_PROGRESS => 'Em andamento',
            self::DONE => 'Concluído',
        };
    }

    public function boardLabel(): string
    {
        return match ($this) {
            self::OPEN => 'TODO',
            self::IN_PROGRESS => 'DOING',
            self::DONE => 'DONE',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::DONE;
    }

    /** @return list<string> */
    public static function openValues(): array
    {
        return [self::OPEN->value, self::IN_PROGRESS->value];
    }
}
