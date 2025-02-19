<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'Por aprovar';
    case CANCELED = 'Cancelado/Não aprovado';
    case APPROVED = 'Aprovado';
    case IN_PROGRESS = 'Em curso';
    case COMPLETED = 'Finalizado';
    case INTERRUPTED = 'Interrompido';
}
