<?php

namespace App\Exceptions;

use RuntimeException;

class NoResponsibleAvailableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Não há responsáveis ativos disponíveis para atribuição automática.');
    }
}
