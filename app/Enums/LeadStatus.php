<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case ProposalSent = 'proposal_sent';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nuevo',
            self::Contacted => 'Contactado',
            self::ProposalSent => 'Propuesta enviada',
            self::Won => 'Ganado',
            self::Lost => 'Perdido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'orange',
            self::Contacted => 'amber',
            self::ProposalSent => 'blue',
            self::Won => 'green',
            self::Lost => 'zinc',
        };
    }

    /**
     * Statuses the admin can pick by hand; "won" only comes from converting to a client.
     *
     * @return list<self>
     */
    public static function selectable(): array
    {
        return [self::New, self::Contacted, self::ProposalSent, self::Lost];
    }
}
