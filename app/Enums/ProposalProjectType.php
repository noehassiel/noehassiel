<?php

namespace App\Enums;

enum ProposalProjectType: string
{
    case Website = 'website';
    case WebApp = 'webapp';
    case Branding = 'branding';
    case Ecommerce = 'ecommerce';
    case MobileApp = 'mobile';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Sitio Web',
            self::WebApp => 'Aplicación Web',
            self::Branding => 'Branding & Identidad',
            self::Ecommerce => 'E-commerce',
            self::MobileApp => 'App Móvil',
            self::Custom => 'Proyecto a la medida',
        };
    }
}
