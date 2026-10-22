<?php

namespace App\Enums;

enum RequirementStatus: string
{
    case Pending = 'pending';
    case ClientMarked = 'client_marked';
    case Received = 'received';

    public function label(): string
    {
        return __("portal.requirement_status.{$this->value}");
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'red',
            self::ClientMarked => 'amber',
            self::Received => 'green',
        };
    }
}
