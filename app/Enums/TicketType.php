<?php

namespace App\Enums;

enum TicketType: string
{
    case Bug = 'bug';
    case Change = 'change';
    case Question = 'question';

    public function label(): string
    {
        return __("portal.tickets.types.{$this->value}");
    }
}
