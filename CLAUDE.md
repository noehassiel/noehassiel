<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.18
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v3
- livewire/volt (VOLT) - v1
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function \_\_construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks

- Add useful array shape type definitions for arrays when appropriate.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== herd rules ===

## Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate URLs for the user to ensure valid URLs.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version-specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== fluxui-free/core rules ===

## Flux UI Free

- This project is using the free edition of Flux UI. It has full access to the free components and variants, but does not have access to the Pro components.
- Flux UI is a component library for Livewire. Flux is a robust, hand-crafted UI component library for your Livewire applications. It's built using Tailwind CSS and provides a set of components that are easy to use and customize.
- You should use Flux UI components when available.
- Fallback to standard Blade components if Flux is unavailable.
- If available, use the `search-docs` tool to get the exact documentation and code snippets available for this project.
- Flux UI components look like this:

<code-snippet name="Flux UI Component Example" lang="blade">
    <flux:button variant="primary"/>
</code-snippet>

### Available Components

This is correct as of Boost installation, but there may be additional components within the codebase.

<available-flux-components>
avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, otp-input, profile, radio, select, separator, skeleton, switch, text, textarea, tooltip
</available-flux-components>

=== livewire/core rules ===

## Livewire

- Use the `search-docs` tool to find exact version-specific documentation for how to write Livewire and Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire Best Practices

- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle Hook Examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>

## Testing Livewire

<code-snippet name="Example Livewire Component Test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>

<code-snippet name="Testing Livewire Component Exists on Page" lang="php">
    $this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
</code-snippet>

=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2

- These things changed in Livewire 3, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives

- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine

- Alpine is now included with Livewire; don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks

- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="Livewire Init Hook Example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });

});
</code-snippet>

=== volt/core rules ===

## Livewire Volt

- This project uses Livewire Volt for interactivity within its pages. New pages requiring interactivity must also use Livewire Volt.
- Make new Volt components using `php artisan make:volt [name] [--test] [--pest]`.
- Volt is a class-based and functional API for Livewire that supports single-file components, allowing a component's PHP logic and Blade templates to coexist in the same file.
- Livewire Volt allows PHP logic and Blade templates in one file. Components use the `@volt` directive.
- You must check existing Volt components to determine if they're functional or class-based. If you can't detect that, ask the user which they prefer before writing a Volt component.

### Volt Functional Component Example

<code-snippet name="Volt Functional Component Example" lang="php">
@volt
<?php
use function Livewire\Volt\{state, computed};

state(['count' => 0]);

$increment = fn () => $this->count++;
$decrement = fn () => $this->count--;

$double = computed(fn () => $this->count \* 2);
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <h2>Double: {{ $this->double }}</h2>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
@endvolt
</code-snippet>

### Volt Class Based Component Example

To get started, define an anonymous class that extends Livewire\Volt\Component. Within the class, you may utilize all of the features of Livewire using traditional Livewire syntax:

<code-snippet name="Volt Class-based Volt Component Example" lang="php">
use Livewire\Volt\Component;

new class extends Component {
public $count = 0;

    public function increment()
    {
        $this->count++;
    }

} ?>

<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
</code-snippet>

### Testing Volt & Volt Components

- Use the existing directory for tests if it already exists. Otherwise, fallback to `tests/Feature/Volt`.

<code-snippet name="Livewire Test Example" lang="php">
use Livewire\Volt\Volt;

test('counter increments', function () {
Volt::test('counter')
->assertSee('Count: 0')
->call('increment')
->assertSee('Count: 1');
});
</code-snippet>

<code-snippet name="Volt Component Test Using Pest" lang="php">
declare(strict_types=1);

use App\Models\{User, Product};
use Livewire\Volt\Volt;

test('product form creates product', function () {
$user = User::factory()->create();

    Volt::test('pages.products.create')
        ->actingAs($user)
        ->set('form.name', 'Test Product')
        ->set('form.description', 'Test Description')
        ->set('form.price', 99.99)
        ->call('create')
        ->assertHasNoErrors();

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();

});
</code-snippet>

### Common Patterns

<code-snippet name="CRUD With Volt" lang="php">
<?php

use App\Models\Product;
use function Livewire\Volt\{state, computed};

state(['editing' => null, 'search' => '']);

$products = computed(fn() => Product::when($this->search,
fn($q) => $q->where('name', 'like', "%{$this->search}%")
)->get());

$edit = fn(Product $product) => $this->editing = $product->id;
$delete = fn(Product $product) => $product->delete();

?>

<!-- HTML / UI Here -->
</code-snippet>

<code-snippet name="Real-Time Search With Volt" lang="php">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />
</code-snippet>

<code-snippet name="Loading States With Volt" lang="php">
    <flux:button wire:click="save" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </flux:button>
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

### Testing

- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests

- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
  <code-snippet name="Basic Pest Test Example" lang="php">
  it('is true', function () {
  expect(true)->toBeTrue();
  });
  </code-snippet>

### Running Tests

- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions

- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
  <code-snippet name="Pest Example Asserting postJson Response" lang="php">
  it('returns all', function () {
  $response = $this->postJson('/api/docs', []);

        $response->assertSuccessful();

    });
    </code-snippet>

### Mocking

- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets

- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== tailwindcss/core rules ===

## Tailwind CSS

- Use Tailwind CSS classes to style HTML; check and use existing Tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc.).
- Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, and group elements logically.
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing

- When listing items, use gap utilities for spacing; don't use margins.

<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>

### Dark Mode

- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind CSS 4

- Always use Tailwind CSS v4; do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.

<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>

### Replaced Utilities

- Tailwind v4 removed deprecated utilities. Do not use the deprecated option; use the replacement.
- Opacity values are still numeric.

| Deprecated | Replacement |
|------------+--------------|
| bg-opacity-_ | bg-black/_ |
| text-opacity-_ | text-black/_ |
| border-opacity-_ | border-black/_ |
| divide-opacity-_ | divide-black/_ |
| ring-opacity-_ | ring-black/_ |
| placeholder-opacity-_ | placeholder-black/_ |
| flex-shrink-_ | shrink-_ |
| flex-grow-_ | grow-_ |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
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
