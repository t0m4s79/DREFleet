<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case AVAILABLE = 'Disponível';
    case UNAVAILABLE = 'Inoperável';
    case IN_SERVICE = 'Em Serviço';
    case HIDDEN = 'Escondido';
    case IN_MAINTENANCE = 'Em manutenção';
}