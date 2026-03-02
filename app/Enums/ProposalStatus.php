<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Sent => 'Enviado',
            self::Accepted => 'Aceptado',
            self::Rejected => 'Rechazado',
            self::Expired => 'Expirado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::Sent => 'blue',
            self::Accepted => 'green',
            self::Rejected => 'red',
            self::Expired => 'amber',
        };
    }
}
