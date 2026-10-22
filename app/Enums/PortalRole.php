<?php

namespace App\Enums;

enum PortalRole: string
{
    /** Sees everything of their client and manages the team. */
    case Owner = 'owner';

    /** Sees what an owner allows: all or selected projects, with or without invoices. */
    case Member = 'member';
}
