<?php

namespace App\Enums;

enum Roles: string
{
    case ADMIN = 'Administrador';
    case MANAGER = 'Gestor';
    case TECHNICIAN = 'Técnico';
    case DRIVER = 'Condutor';
    case NONE = 'Nenhum';
}
