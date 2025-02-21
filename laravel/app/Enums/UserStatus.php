<?php

namespace App\Enums;

enum UserStatus: string
{
    case AVAILABLE = 'Disponível';
    case UNAVAILABLE = 'Indisponível';
    case IN_SERVICE = 'Em Serviço';
    case HIDDEN = 'Escondido';
}