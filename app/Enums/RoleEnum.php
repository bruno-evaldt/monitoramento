<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case SINDICO = 'sindico';
    case MORADOR = 'morador';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrador',
            self::SINDICO => 'Síndico',
            self::MORADOR => 'Morador',
        };
    }
}
