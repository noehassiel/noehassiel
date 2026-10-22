<?php

namespace App\Enums;

/**
 * The services noehassiel offers, plus a catch-all for custom projects.
 * Also used to guide AI-drafted proposals.
 */
enum ProposalProjectType: string
{
    case WebDesign = 'web_design';
    case WebResearch = 'web_research';
    case WebDesignAndDevelopment = 'web_design_development';
    case MobileAppDesignAndDevelopment = 'mobile_design_development';
    case WebAndMobile = 'web_mobile';
    case ProjectManagement = 'project_management';
    case AiImplementation = 'ai_implementation';
    case TaskAppOrganization = 'task_app_organization';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::WebDesign => 'Diseño web',
            self::WebResearch => 'Investigación web (UX research)',
            self::WebDesignAndDevelopment => 'Diseño y desarrollo web',
            self::MobileAppDesignAndDevelopment => 'Diseño y desarrollo de app móvil',
            self::WebAndMobile => 'Diseño y desarrollo web + móvil',
            self::ProjectManagement => 'Project management',
            self::AiImplementation => 'Implementación de IA',
            self::TaskAppOrganization => 'Organización en apps de tareas',
            self::Custom => 'Proyecto a la medida',
        };
    }

    /** @return array<int, self> The concrete services, without the custom catch-all. */
    public static function services(): array
    {
        return array_values(array_filter(self::cases(), fn (self $type): bool => $type !== self::Custom));
    }

    /** What the service usually covers, so the AI scopes it realistically. */
    public function scope(): string
    {
        return match ($this) {
            self::WebDesign => 'UI/UX design of a website in Figma: structure, wireframes, visual design, responsive layouts, design system. No code.',
            self::WebResearch => 'Research before building: competitor and market analysis, user interviews, audits of an existing site (UX, SEO, performance), findings and recommendations.',
            self::WebDesignAndDevelopment => 'End-to-end website or web app: design plus Laravel development, CMS or admin panel, deployment, performance (Lighthouse 95+).',
            self::MobileAppDesignAndDevelopment => 'Native mobile app for iOS and Android: design plus development, backend/API, store publishing.',
            self::WebAndMobile => 'A product with both a web platform and a mobile app sharing one backend: design, development, deployment and store publishing.',
            self::ProjectManagement => 'Managing a project (software or not): planning, roadmap, weekly follow-ups, team and vendor coordination, reporting.',
            self::AiImplementation => 'Bringing AI into a business: assistants, automations, document processing, integrating LLMs into existing systems.',
            self::TaskAppOrganization => 'Setting up and organizing a team in task apps (Notion, Linear, ClickUp, Asana…): workspace structure, workflows, templates, training.',
            self::Custom => 'Anything that does not fit one service or combines several in a non-standard way.',
        };
    }
}
