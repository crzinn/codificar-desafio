<?php

namespace App\Exceptions;

use App\Enums\TicketStatus;
use RuntimeException;

class InvalidTicketStatusTransitionException extends RuntimeException
{
    public function __construct(TicketStatus $from, TicketStatus $to)
    {
        parent::__construct(sprintf(
            'Não é permitido alterar o status de %s para %s.',
            $from->label(),
            $to->label(),
        ));
    }
}
