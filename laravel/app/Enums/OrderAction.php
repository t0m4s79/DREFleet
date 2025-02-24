<?php

namespace App\Enums;

enum OrderAction: string
{
    case INTERRUPT = 'stop';
    case FINISH = 'finish';
}
