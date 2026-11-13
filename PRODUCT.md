# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

One Laravel app serves three audiences, each on its own surface:

- **Prospective clients (public landing page, `/`).** Primary: Mexican and LATAM businesses and brands that are ready to invest in a quality digital presence and are tired of cheap, generic work. Secondary: international clients (remote-first). Clients brought in through Estoria Studio (León, Gto.) also land here. Their job is to decide whether noehassiel is the right person to trust with their project, and then make contact through the project form.
- **Clients and their teams (client portal, `/portal`, `/q/{token}`).** Business owners and their staff, invited per client with roles. They review and accept private proposals, pay the deposit and balance online, upload pending deliverables, follow project progress, download invoices, review a live preview of their site and request changes through an AI chat.
- **Hassiel himself (admin: `/dashboard`, `/leads`, `/proposals`, `/clients`, `/projects`).** The single operator. He runs the leads pipeline, writes and edits proposals (including with AI), turns leads into clients, manages projects, shared folders and deliverables checklists, and opens a client's preview read-only.

## Product Purpose

noehassiel is the personal studio of Noe Hassiel Monterrosas Bandala (Hassiel), a fullstack Laravel developer, UI/UX designer and project manager based in Puebla, Mexico. The product sells his work and then delivers it: the landing page wins the client, and the portal and admin carry the whole relationship from proposal to payment to launch.

The wider mission is to raise the digital standard, built in Mexico and crafted for the world, by proving that well-crafted digital experiences are investments, not luxuries. The landing page succeeds when the right client reaches out. The portal succeeds when a client gets through proposal, deliverables, preview, approval and payment without needing a call to understand where things stand.

## Positioning

The order is confirmed:

1. **Craft first: designer and developer in one person.** One person owns design, code and project management end to end. The work itself is the proof.
2. **Technical edge: web plus native apps from one codebase.** Native iOS and Android apps built with NativePHP Super Native (real SwiftUI and Jetpack Compose, no webview) grow from the same Laravel monorepo as the website. When a client needs an app, it grows inside the existing project instead of starting from zero.
3. **Proof of seriousness: the client process is itself a product.** Clients get private proposals, a portal, live previews with AI-triaged change requests (classified against the accepted proposal's scope) and online payment with meses sin intereses. Few independent developers in the market can offer this.

## Operating Context

- **Sales flow:** a lead arrives through the landing form, enters the admin pipeline, and receives a proposal shared only through a private link (`/q/{token}`). The client can accept it, request changes or cancel it. Terms are 50/50: a deposit (Anticipo) before work starts and the balance at the end. Card deposits from 3,000 MXN can use meses sin intereses (Stripe via Cashier).
- **Service types offered:** web design, web research, web design and development, mobile app design and development, web plus mobile, project management, AI implementation, task-app organization, and custom work.
- **Delivery flow:** after acceptance the client gets invited to the portal, where they work through a deliverables checklist (uploads up to 50MB), see shared folders, open tickets and receive email reminders. A live preview of their site is served from `*.preview.noehassiel.com` and opened inside the portal, with an AI chat panel. In-scope requests (copy, images, section order, bugs) become Linear issues. Out-of-scope requests are sent for a quote and never priced by the AI. Revision rounds are limited. Projects and requests are mirrored to Linear in both directions.
- **Launch:** the plan is to launch on balance payment through GitHub Actions. For mobile, TestFlight or Internal Testing serves as the preview.
- **Hassiel's other work:** PM at Console Clauger, and an ongoing collaboration with Estoria Studio.

## Capabilities and Constraints

- Stack: Laravel 13, Livewire 3 + Volt, Tailwind CSS 4, SheafUI components, GSAP, Laravel AI SDK, Cashier (Stripe), Fortify with passkeys and 2FA, Cloudflare R2 for invoice storage. Deployed on Laravel Cloud.
- Locales: `es` and `en`. Spanish is the default for client-facing work, English for international clients, code and technical contexts.
- The admin and portal are kept in dark mode regardless of OS theme (a deliberate commit).
- Performance target: Lighthouse 95+ on every metric for public pages.
- Grid: 8px base, 12-column layouts on the web.
- **Open:** whether the portal preview's element picker and automatic launch on payment ship as planned (roadmap phases 2–4). Mobile store submission on payment depends on whether Bifrost offers an API.

## Brand Commitments

The binding source of truth is `NOEHASSIEL.md` (the newer, worldwide version) together with `CLAUDE.md`. In short:

- **Name:** always `noehassiel`: lowercase, one word, never capitalized or split. Tagline: `artisan developer`, lowercase, set in Geist Mono. The tagline is also a nod to `php artisan`.
- **Isotipo:** `n/h` with the slash in the accent color and a blinking cursor after the `h` (a solid cursor in print). The favicon cursor already blinks when the tab is hidden.
- **Personality, in order:** close and human → sophisticated and premium → quietly rebellious → technical and reliable. Archetypes: Craftsman 60%, Sage 25%, Rebel 15%.
- **Voice:** direct and warm, "como un amigo que sabe de lo que habla". No jargon or corporate-speak, and none of the words synergies, leveraging, scalable solutions, or disruptive used as a buzzword. Use "we" for collaboration and "I" when owning expertise. Code comments are always in English.
- **Pinned visual identity (binding, recorded rather than expanded here):** Obsidian `#0A0A0A`, Warm Stone `#FAFAF9`, Tech Ember `#FF420B`, Artisan Brass `#C8A87C`, plus the secondary palette and the Ember and Brass scales in `NOEHASSIEL.md`. Typefaces are Cabinet Grotesk and Geist Mono. Imagery is warm, desaturated and editorial with subtle grain. Icons are line-only at a 1.5px stroke. Motion uses smooth ease-out curves and is never bouncy.
- **References Hassiel named:** valentincheval.design, thierrychopain.com, hematogenix.com, valientebrands.com, untold.site. For the portal preview, the reference is Laravel Herd's preview window: dark chrome, a framed page with rounded corners, a small floating toolbar and a chat panel at the side.

## Evidence on Hand

- **Real portfolio** (`config/portfolio.php`, screenshots in `public/images/work/`): Console (Clauger US, industrial refrigeration, 2023–), Return Policy (Hassiel's own loyalty SaaS, 2026), The Vine Line (fashion e-commerce, 2026), Grupo Guiar (real estate, with Estoria Studio, 2025), Ibero León (Universidad Iberoamericana, with Estoria Studio, 2020). All of them link to live sites.
- **Portraits** of Hassiel in `public/images/me/` (hero, about, cutouts, scene variants).
- **Pilot preview client:** IICC (`IiccPreviewDemoSeeder`).
- **Absent, do not fabricate:** testimonials, client quotes, logo walls, performance or business metrics, pricing tables and awards. None exist in the repo.

## Product Principles

1. **The work is the argument.** Show real projects and the process behind them before making any claim.
2. **A friend who knows, never a vendor.** Every surface, including errors, emails and payment screens, speaks plainly and warmly in the client's language.
3. **The process is part of the craft.** The portal, proposals and previews deserve the same care as the landing page, because clients judge the craft by them too.
4. **Clarity about scope and money.** Clients always know what is included, what has been paid, what is pending and what comes next. The AI triages requests but never sets prices.
5. **One codebase, every platform.** Web and native grow together. Design decisions should not trap a project on one platform.
