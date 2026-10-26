<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return __("portal.tickets.statuses.{$this->value}");
    }

    /**
     * Map a Linear workflow state type (triage, backlog, unstarted, started, completed, canceled) to ours.
     */
    public static function fromLinearStateType(string $type): self
    {
        return match ($type) {
            'started' => self::InProgress,
            'completed' => self::Resolved,
            'canceled' => self::Closed,
            default => self::Open,
        };
    }
}
