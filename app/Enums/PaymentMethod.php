<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Transfer = 'transfer';
    case Cash = 'cash';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Transfer => 'Transferencia',
            self::Cash => 'Efectivo',
            self::Card => 'Tarjeta',
        };
    }
}
