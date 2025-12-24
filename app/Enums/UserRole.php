<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case DOCTOR = 'doctor';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::DOCTOR => 'Doctor',
            self::ADMIN => 'Administrator',
        };
    }
}
