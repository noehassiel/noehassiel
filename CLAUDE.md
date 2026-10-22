<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== volt/core rules ===

# Livewire Volt

- Single-file Livewire components: PHP logic and Blade templates in one file.
- Always check existing Volt components to determine functional vs class-based style.
- IMPORTANT: Always use `search-docs` tool for version-specific Volt documentation and updated code examples.
- IMPORTANT: Activate `volt-development` every time you're working with a Volt or single-file component-related task.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>

# noehassiel — Brand Identity Project

## About This Project

This project defines and develops the personal brand identity for **noehassiel** (Noe Hassiel Monterrosas Bandala), known as Hassiel. This is a living brand system — everything here is the source of truth.

## Who is noehassiel

- **Name:** noehassiel (always lowercase, one word, no spaces/dots/hyphens)
- **Tagline:** artisan developer (always lowercase, set in Geist Mono)
- **Role:** Fullstack developer (Laravel) + UI/UX designer + Project Manager
- **Location:** Puebla, Mexico
- **Current work:** PM at Console Clauger, collaborates with Estoria Studio (León, Gto.)
- **Language:** Bilingual — Spanish for clients/market, English for code/technical/international contexts

## Brand Essence

A developer with the soul of a designer. A designer with the soul of a developer. noehassiel exists at the intersection of code and design — the "unicorn" who bridges technical architecture and human-centered experience. The mission is to elevate the digital standard in Mexico, proving that well-crafted digital experiences are investments, not luxuries.

## Brand Personality Hierarchy (in order of dominance)

1. **Cercana y humana** — The foundation. Speaks to you, not at you. A friend who's an expert.
2. **Sofisticada y premium** — The execution. Polished, refined, worth every peso.
3. **Rebelde y disruptiva** — The attitude. Quiet disruption through excellence.
4. **Técnica y confiable** — The silent base. Deep expertise that ensures everything works.

## Brand Archetypes

- **The Craftsman (60%)** — Builds with care, detail, pride. The "artisan" in artisan developer.
- **The Sage (25%)** — Educates and empowers clients. Not condescending — enabling.
- **The Rebel (15%)** — Challenges the Mexican digital market's status quo.

## Voice & Tone

- **Voice:** Direct, warm, no pretension. Like a knowledgeable friend. No jargon, no corporate-speak.
- **Communication style:** "Como un amigo que sabe de lo que habla" — direct and without fluff, but always warm.
- **Bilingual strategy:** Spanish primary for client-facing. English strategic for tech credibility and code. The blend is natural, never forced.
- **Code comments:** Always in English.
- **Pronouns:** "We" for collaboration, "I" when owning expertise.

### Tone by Context

| Context           | Tone                                                          |
| ----------------- | ------------------------------------------------------------- |
| Social Media      | Casual, warm, educational. Coffee-talk energy.                |
| Client Proposals  | Professional but human. Confident, data-backed, story-driven. |
| Portfolio/Cases   | Minimal text, max impact. Show the WHY and process.           |
| Technical Content | Clear, structured, bilingual. Mastery through simplicity.     |
| Networking        | Approachable. Lead with curiosity about their challenges.     |

## Visual Identity

### Color Palette — Hybrid System

**Primary:**
| Name | Hex | Usage |
|------|-----|-------|
| Obsidian | `#0A0A0A` | Backgrounds, authority |
| Warm Stone | `#FAFAF9` | Light backgrounds, breathing space |
| Tech Ember | `#FF420B` | Primary accent, CTAs, energy, impact |
| Artisan Brass | `#C8A87C` | Secondary accent, warmth, subtle details |

**Secondary:**
| Name | Hex | Usage |
|------|-----|-------|
| Charcoal | `#2D2D2D` | Headings, emphasis |
| Slate | `#6B6B6B` | Body text, descriptions |
| Silver | `#B0B0B0` | Captions, metadata |
| Sand | `#F0EBE3` | Subtle backgrounds |

**Ember Scale:** `#FFE8E0` → `#FFB39A` → `#FF7A55` → `#FF420B` → `#CC3509` → `#991F00`

**Brass Scale:** `#F5EDE3` → `#E8D5B5` → `#D4BC94` → `#C8A87C` → `#9E7E54` → `#7A5F3C`

**Usage Ratio:** 65% Base (Obsidian/Stone) | 20% Content (Charcoal/Slate) | 10% Ember | 5% Brass

### Typography

| Role             | Typeface            | Weights                 | Source               |
| ---------------- | ------------------- | ----------------------- | -------------------- |
| Primary          | **Cabinet Grotesk** | 400, 500, 700, 800, 900 | fontshare.com (free) |
| Secondary / Code | **Geist Mono**      | 400, 500, 600, 700      | Google Fonts (free)  |

**Why Cabinet Grotesk:** Geometric grotesque with distinctive character. Not Inter, not overused. Premium, modern, intentional — the artisan choice.

**Why Geist Mono:** Created by Vercel for the modern web. Engineered for interfaces and code. Connects to the dev ecosystem naturally.

**Type Scale:**

- Display: 48-64px / Cabinet Grotesk Bold-Black
- H1: 32-40px / Cabinet Grotesk Bold
- H2: 24-28px / Cabinet Grotesk Bold
- H3: 18-20px / Cabinet Grotesk Medium
- Body: 16px / Cabinet Grotesk Regular
- Code/Meta: 14px / Geist Mono Regular
- Caption: 12px / Geist Mono Regular

### Logo & Isotipo

- **Logotype:** "noehassiel" in Cabinet Grotesk Bold, always lowercase. Tagline "artisan developer" below in Geist Mono, colored in Tech Ember.
- **Isotipo (Hybrid 1):** Letters "n" and "h" separated by a forward slash (/) in accent color, followed by a blinking cursor after the "h". The slash references code/file paths. The cursor gives life in digital — static solid in print. The "nh" stands for noehassiel.
- **Color versions:** White on dark (default), Dark on light, Ember accent on dark (special use).
- **Naming rule:** Always lowercase. Never Noehassiel, NOEHASSIEL, or NoeHassiel. One word.

### Photography & Imagery Style

- **Mood:** Warm, intimate, contemplative. Natural light, golden hour.
- **Subjects:** Hands on keyboards, code on screens, workspaces, process, textures.
- **Treatment:** Desaturated with warm tones. No oversaturated or neon.
- **Composition:** Negative space, asymmetric, shallow depth of field, editorial.
- **Icons:** Line only, 1.5px stroke, geometric, minimal.
- **Motion:** Smooth ease-out curves. Intentional. Never bouncy.
- **Texture:** Subtle grain (3-5% noise opacity) for artisanal warmth.

## Design References

These sites inform the aesthetic and communication direction:

- [Valentin Cheval](https://valentincheval.design/) — Immersive web design as experience
- [Thierry Chopain](https://thierrychopain.com/) — "Artisan digital" voice, human copy with character
- [Hematogenix](https://www.hematogenix.com/) — Corporate seriousness with clean design
- [Valiente Brands](https://valientebrands.com/) — Mexican branding with attitude
- [Untold](https://untold.site/en) — Storytelling as brand tool

## Target Audience

**Primary:** Mexican businesses and brands ready to invest in quality digital presence — tired of cheap, generic solutions.
**Secondary:** Clients through Estoria Studio seeking premium collaborative development.

## Content Pillars (Social Media)

1. **Craft & Process** (40%) — Showing the work
2. **Education** (30%) — Why quality matters
3. **Industry Commentary** (20%) — Challenging the status quo
4. **Personal & Human** (10%) — The person behind the brand

## Key Brand Phrases & Vocabulary

- "Crafted with intention"
- "Every pixel matters. Every line of code has purpose."
- "artisan developer"
- Use: crafted, confected, built by hand, bespoke, tailor-made
- Avoid: synergies, leveraging, scalable solutions, disruptive (as buzzword)

## Technical Context

- **Stack:** Laravel, Tailwind CSS, GSAP/Framer Motion
- **The "artisan" easter egg:** `php artisan` is Laravel's CLI command — the word "artisan" bridges the brand identity with the actual development tool used daily.
- **Grid:** 8px base grid, 12-column layouts for web
- **Performance target:** Lighthouse 95+ on all metrics

## Project Files

- `noehassiel_brandbook_v2.pdf` — Complete brand guidelines document (14 pages)
- `noehassiel_brand_explorer.jsx` — Interactive typography and color exploration tool
- `noehassiel_isotipo_explorer.jsx` — Isotipo variant exploration with Hybrid 1 selected

## Working Rules for Claude

- All code comments in English
- Hassiel prefers brutally honest, direct, rational feedback — challenge his thinking, point out blind spots
- When writing client-facing copy: warm, direct, bilingual-natural
- When writing code: clean, well-commented, Laravel conventions
- When discussing brand: always reference this document as source of truth
- Never write the brand name as anything other than lowercase "noehassiel"
- The tagline is always "artisan developer" — lowercase, in Geist Mono context
