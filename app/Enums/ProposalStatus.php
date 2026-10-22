<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case ChangesRequested = 'changes_requested';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Sent => 'Enviado',
            self::ChangesRequested => 'Cambios solicitados',
            self::Accepted => 'Aceptado · pago pendiente',
            self::Rejected => 'Rechazado',
            self::Expired => 'Expirado',
            self::Paid => 'Anticipo pagado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::Sent => 'blue',
            self::ChangesRequested => 'amber',
            self::Accepted => 'green',
            self::Rejected => 'red',
            self::Expired => 'amber',
            self::Paid => 'green',
        };
    }
}
