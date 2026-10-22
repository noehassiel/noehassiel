<?php

namespace App\Enums;

enum ProposalEventType: string
{
    case ChangesRequested = 'changes_requested';
    case Updated = 'updated';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    /** How the client reads it on their proposal page. */
    public function label(): string
    {
        return match ($this) {
            self::ChangesRequested => 'Pediste cambios',
            self::Updated => 'Actualicé la propuesta',
            self::Accepted => 'Aceptaste la propuesta',
            self::Rejected => 'Cancelaste la propuesta',
        };
    }

    /** How the admin reads it. */
    public function adminLabel(): string
    {
        return match ($this) {
            self::ChangesRequested => 'El cliente pidió cambios',
            self::Updated => 'Actualizaste la propuesta',
            self::Accepted => 'El cliente aceptó',
            self::Rejected => 'El cliente canceló',
        };
    }

    public function isFromClient(): bool
    {
        return $this !== self::Updated;
    }
}
