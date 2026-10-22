<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Brief = 'brief';
    case Design = 'design';
    case Development = 'development';
    case Review = 'review';
    case Delivered = 'delivered';
    case Paused = 'paused';

    public function label(): string
    {
        return __("portal.project_status.{$this->value}");
    }

    public function color(): string
    {
        return match ($this) {
            self::Brief => 'zinc',
            self::Design => 'amber',
            self::Development => 'blue',
            self::Review => 'violet',
            self::Delivered => 'green',
            self::Paused => 'red',
        };
    }

    /**
     * Whether the client still sees the project as ongoing work.
     */
    public function isActive(): bool
    {
        return $this !== self::Delivered;
    }
}
