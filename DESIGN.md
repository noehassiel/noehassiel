---
name: noehassiel
description: artisan developer. Code-native warmth on obsidian, lit by a single ember.
colors:
  obsidian: "#0A0A0A"
  warm-stone: "#FAFAF9"
  tech-ember: "#FF420B"
  ember-deep: "#CC3509"
  ember-soft: "#FF7A55"
  artisan-brass: "#C8A87C"
  charcoal: "#2D2D2D"
  slate: "#6B6B6B"
  silver: "#B0B0B0"
  sand: "#F0EBE3"
  sidebar-black: "#0D0D0D"
  card-black: "#111111"
  surface: "#121212"
  inset: "#191919"
  tertiary: "#222222"
  hairline: "#1C1C1C"
  field-edge: "#262626"
  heading-ink: "#EDEDEC"
  body-ink: "#A6A6A3"
  muted-ink: "#7E7E7B"
typography:
  display:
    fontFamily: "CabinetGrotesk-Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(64px, min(11.5vw, 21svh), 200px)"
    fontWeight: 900
    lineHeight: 0.86
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "CabinetGrotesk-Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(32px, 4.5vw, 56px)"
    fontWeight: 800
    lineHeight: 1.05
    letterSpacing: "-0.025em"
  title:
    fontFamily: "CabinetGrotesk-Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(28px, 3vw, 40px)"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "-0.02em"
  subtitle:
    fontFamily: "CabinetGrotesk-Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "22px"
    fontWeight: 700
    lineHeight: 1.2
  body-lead:
    fontFamily: "CabinetGrotesk-Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "18px"
    fontWeight: 400
    lineHeight: 1.6
  body:
    fontFamily: "CabinetGrotesk-Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Geist Mono, ui-monospace, SFMono-Regular, monospace"
    fontSize: "13px"
    fontWeight: 500
    lineHeight: 1.4
  caption:
    fontFamily: "Geist Mono, ui-monospace, SFMono-Regular, monospace"
    fontSize: "12px"
    fontWeight: 400
    lineHeight: 1.4
  eyebrow:
    fontFamily: "Geist Mono, ui-monospace, SFMono-Regular, monospace"
    fontSize: "11px"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "0.18em"
rounded:
  tick: "2px"
  field: "10px"
  tile: "12px"
  card: "16px"
  dock-widget: "18px"
  shell: "22px"
  dock: "24px"
  pill: "999px"
spacing:
  xs: "8px"
  sm: "16px"
  md: "24px"
  lg: "48px"
  xl: "80px"
  gutter: "clamp(16px, 4vw, 48px)"
  section: "clamp(80px, 12vw, 160px)"
components:
  button-primary:
    backgroundColor: "{colors.tech-ember}"
    textColor: "#FFFFFF"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "0 24px"
    height: "48px"
  button-primary-hover:
    backgroundColor: "{colors.ember-deep}"
  button-dark:
    backgroundColor: "{colors.obsidian}"
    textColor: "{colors.warm-stone}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "0 24px"
    height: "48px"
  button-dark-hover:
    backgroundColor: "{colors.charcoal}"
  button-light:
    backgroundColor: "{colors.warm-stone}"
    textColor: "{colors.obsidian}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "0 24px"
    height: "48px"
  button-light-hover:
    backgroundColor: "{colors.sand}"
  button-small:
    rounded: "{rounded.pill}"
    padding: "0 18px"
    height: "40px"
  chip:
    textColor: "{colors.warm-stone}"
    rounded: "{rounded.pill}"
    padding: "0 16px"
    height: "40px"
  chip-selected:
    backgroundColor: "{colors.tech-ember}"
    textColor: "#FFFFFF"
  input-field:
    textColor: "{colors.warm-stone}"
    typography: "{typography.body}"
    rounded: "{rounded.field}"
    padding: "14px 16px"
  portal-card:
    backgroundColor: "{colors.card-black}"
    rounded: "{rounded.card}"
  admin-widget:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.shell}"
    padding: "6px"
  admin-inset:
    backgroundColor: "{colors.inset}"
    rounded: "{rounded.card}"
  admin-chip:
    backgroundColor: "{colors.tertiary}"
    textColor: "{colors.body-ink}"
    rounded: "{rounded.pill}"
  process-card:
    backgroundColor: "{colors.warm-stone}"
    rounded: "{rounded.card}"
    padding: "28px"
  work-media:
    backgroundColor: "{colors.charcoal}"
    rounded: "{rounded.tile}"
---

# Design System: noehassiel

## Overview

**Creative North Star: "The Artisan's Terminal"**

The whole system grows from the isotipo `n/h_`: an ember slash and a blinking caret. Every surface is a terminal session run by a craftsman. The room is obsidian, metadata is set in Geist Mono like command output, and one ember accent marks the line that matters right now. The precision is human. Cabinet Grotesk carries the voice in big, confident and slightly heavy cuts, while the mono layer stays quiet and exact. A 4% film grain sits over everything so the screen never looks sterile.

The same world runs at three intensities. The **landing page** is a performance: a full-bleed portrait in an obsidian room, a low ember light rising from the floor, giant uppercase headlines that rise line by line, and sections that switch between obsidian, warm stone and sand. The **portal and admin** are the same terminal at working volume. They are dark only, built from stepped near-black layers, with mono eyebrows, ember used for focus and for the current state, and nothing ornamental that slows a task. A floating **dock** (brand mark, section apps, language switch, live widgets) and the **Samantha** voice window are the system's signature: a small personal OS wrapped around the content.

Components are **precise and tactile**: pills that press, nested shells, hairline edges. They are confident but never loud. Motion follows one long ease-out curve, intentional and never bouncy.

**Key Characteristics:**
- Obsidian base with one ember accent per view. Brass is a rare, warm whisper.
- Cabinet Grotesk for voice, Geist Mono for metadata, labels, buttons and numbers.
- The `n/h_` caret and slash recur as motif: blinking cursor, ember slash bullets, typed brand messages.
- Depth comes from tonal layers and hairlines. Shadows only for things that float.
- A 4% grain overlay on every branded surface.
- Fully bilingual: both languages render and `html[data-lang]` shows one.

## Colors

The palette is mostly near-black and warm white. Ember is the single voltage and brass is the warm afterglow.

### Primary
- **Tech Ember** (`tech-ember`): the only call to action and the only "now". It colors primary buttons, the slash in `n/h`, section labels, focus outlines, the active nav bar, the current stepper segment, selected chips and the hero accent line. On the landing hero it also appears as a radial lamp glow at about 12–22% alpha.
- **Ember Deep** (`ember-deep`): hover and pressed states of ember fills. Never used as a resting color.
- **Ember Soft** (`ember-soft`): error text and invalid borders on dark surfaces. It reads as a warning without competing with the ember CTA.

### Secondary
- **Artisan Brass** (`artisan-brass`): warmth in small doses. It colors the surname in the hero intro, process step numbers, avatar initials in the admin and the "placeholder" pill outline. Never used for fills or buttons.

### Neutral
- **Obsidian** (`obsidian`): the room. Used for the hero, dark sections, portal and admin backgrounds, and the wells of form fields in the admin.
- **Warm Stone** (`warm-stone`): light sections, primary text on dark, and process cards sitting on sand.
- **Sand** (`sand`): the alternate light section (Process). It gives a paper-like pause between stone and obsidian.
- **Charcoal** (`charcoal`): body text on light surfaces, empty work media, the success panel, and the dark-button hover.
- **Slate** (`slate`): secondary copy on light surfaces, portal eyebrows and field hints.
- **Silver** (`silver`): secondary copy on dark surfaces, captions, work meta, done stepper segments.
- **Working-surface ladder** (portal and admin, dark only): `sidebar-black` → `card-black` / `surface` → `inset` → `tertiary`. Each step is one layer closer to the user. Borders are `hairline` (`#1C1C1C`) or `rgba(255,255,255,0.07)`, and field edges are `field-edge`.
- **Admin inks**: `heading-ink` for titles, `body-ink` for copy, `muted-ink` for eyebrows and meta. All are slightly warm greys, never pure white.

### Named Rules
**The One Ember Rule.** Each view has one ember destination (one primary CTA or one current state). Everything else is neutral. Ember is about 10% of the palette budget at most, and its scarcity is what makes it read as "click here" or "you are here".

**The Brass Whisper Rule.** Brass appears only in text, numbers, initials or 1px outlines. It is never a fill, never a button, never more than 5% of a screen.

**The Warm Ink Rule.** Text on dark is never `#FFFFFF`. Use Warm Stone or `heading-ink`. The only pure white is the label inside an ember fill.

## Typography

**Display Font:** Cabinet Grotesk, variable (with `ui-sans-serif, system-ui`)
**Label/Mono Font:** Geist Mono, variable (with `ui-monospace, SFMono-Regular`)

**Character:** A geometric grotesque with attitude, set heavy and tight, paired with a mono that reads like command output. The grotesque speaks and the mono annotates.

### Hierarchy
- **Display** (900, `clamp(64px, min(11.5vw, 21svh), 200px)`, 0.86, uppercase, −0.025em): hero headline only. Each line is its own mask so the words rise into place, with an ember accent line. It is bounded by viewport height so short laptops keep the hero in one view.
- **Headline** (800, `clamp(32px, 4.5vw, 56px)`, 1.05, −0.025em, `text-wrap: balance`): section titles.
- **Title** (700, `clamp(28px, 3vw, 40px)`, 1.1, −0.02em): work item names. Service titles use the same weight at `clamp(24px, 2.5vw, 32px)`.
- **Subtitle** (700, 22px): process step titles and card titles.
- **Body lead** (400, 18px, 1.6, `text-wrap: pretty`): section leads in Slate on light or Silver on dark.
- **Body** (400, 16px, 1.6): running copy. Measure is about 40–44ch in hero and work summaries.
- **Label** (Geist Mono 500, 13px): buttons, links, section labels and service numbers.
- **Caption** (Geist Mono 400, 12px): meta, field labels and tags.
- **Eyebrow** (Geist Mono 500, 11px, 0.18em, uppercase): portal and admin section eyebrows and nav group labels. The admin widget eyebrow is a tighter variant (10.5px, 0.14em).

### Named Rules
**The Mono Annotates Rule.** Geist Mono is reserved for what a terminal would print: labels, numbers, meta, buttons, timestamps and the tagline `artisan developer`. Headlines and prose are always Cabinet Grotesk.

**The Lowercase Name Rule.** `noehassiel` and `artisan developer` are always lowercase, even inside uppercase display settings.

## Layout

- **Container:** max 1200px, centered, with a fluid side gutter of `clamp(16px, 4vw, 48px)`. The header, hero and container all share this gutter so left edges line up.
- **Section rhythm:** vertical padding of `clamp(80px, 12vw, 160px)`. Section headers are capped at 720px with `clamp(48px, 6vw, 80px)` beneath.
- **Base grid:** 8px. The common steps are 8, 16, 24, 48 and 80, with 12, 20 and 28 used for tight internal fits.
- **Hero:** full viewport (`100svh`) grid with a giant headline bottom-left spanning all rows and a 260–320px detail column on the right (services list, help link, lead, CTAs). Content clears both the header (112px top) and the dock (dock height + 56px bottom). It pins on scroll and Work slides over it.
- **Work:** alternating 7fr / 5fr rows above 900px (media flips side on even rows), stacked below.
- **Services:** a ruled list (hairlines in 12% obsidian) with `80px / 4fr / 6fr` columns on desktop and a 48px number column on mobile.
- **Process:** `auto-fit, minmax(220px, 1fr)` cards on sand.
- **Dock clearance:** the landing body reserves `dock-height (72px) + 48px` at the bottom, so nothing important sits under the dock.
- **Admin:** a sidebar plus a 64px header with a blurred obsidian bar. Content cards sit in a single responsive grid.

## Elevation & Depth

The system is flat by default and gets its depth from tone. On working surfaces, depth is a ladder of near-blacks: page `obsidian` → card `card-black` / `surface` → panel `inset` → control `tertiary`, each separated by a 1px hairline. The admin's signature "nested shell" draws its outer ring with a box-shadow (`0 0 0 6px surface, 0 0 0 7px border`). That is a drawn ring, not a lift. Real cast shadows exist only for things that physically float above the page.

### Shadow Vocabulary
- **Dock float** (`box-shadow: 0 20px 50px -20px rgba(10,10,10,0.5)` with `backdrop-filter: blur(16px)` on `rgba(10,10,10,0.92)`): the floating dock.
- **Modal float** (`box-shadow: 0 24px 80px rgba(0,0,0,0.6)` over a `rgba(0,0,0,0.6)` scrim): admin modals.
- **Samantha window** (`box-shadow: 0 30px 60px -20px rgba(90,30,10,0.5)`): the docked voice app. It is the only warm-tinted shadow.
- **Focus halo** (`box-shadow: 0 0 0 3px color-mix(in srgb, ember 18%, transparent)`): admin field focus. This is state, not elevation.

### Named Rules
**The Tonal Ladder Rule.** Surfaces never lift with shadows. A card that needs emphasis moves one step up the ladder (`inset` → `tertiary`) and takes an ember-tinted border (35–45% ember mix) on hover. Cast shadows are reserved for the dock, modals and the Samantha window.

**The Grain Rule.** Every branded body (landing, portal, admin) carries the fixed fractal-noise overlay at 4% opacity. It sits above the background and below the content, and never captures pointer events.

## Shapes

Corners are generous and consistent, and everything you can press is a pill.

- **Pills** (`pill`, 999px): every button, chip, tag, avatar, the skip link, the language switch and the stepper segments.
- **Cards** (`card`, 16px): portal cards, admin insets and cards, process steps, the success panel and empty states.
- **Shells** (`shell`, 22px): the outer ring of admin widgets and modals, wrapping a 16px inset with 6px between them (outer radius = inner radius + gap).
- **Dock** (`dock`, 24px outer) around `dock-widget` (18px) tiles, with `tile` (12px) app buttons inside. These follow the same concentric rule.
- **Fields** (`field`, 10px on the landing; the admin uses `--radius-field` 0.5rem).
- **Media** (`tile`, 12px): work screenshots.
- **Ticks** (`tick`, 2px): admin progress tick bars and the active-nav indicator.
- Icons are line-only with a 1.5px stroke. Dashed 1px borders mark empty or placeholder states (`admin-widget-icon`, `admin-dashed`).

**The Concentric Corners Rule.** A nested container's radius equals its parent's radius minus the padding between them (22 → 16 at 6px, 24 → 18 at 6px). Never put the same radius on a parent and its child.

## Components

### Buttons
Precise and tactile: mono-labelled pills that press.
- **Shape:** full pill (999px), 48px tall (40px for the small variant), with a 1.5px border slot that is transparent by default.
- **Primary:** Tech Ember fill with a white Geist Mono 500 label at 14px. It is the one CTA per view.
- **Dark / Light:** Obsidian with stone text, or stone with obsidian text, for secondary actions on the opposite ground.
- **Ghost:** `currentColor` border on a transparent fill, with a 5% obsidian wash on hover.
- **Hover / Active:** fills step down a tone (ember → ember-deep, obsidian → charcoal, stone → sand) over 200ms. Active scales to 0.98 on the brand ease-out curve. Disabled is 60% opacity.
- **Admin:** SheafUI primary buttons are restyled into ember pills (`admin-btn-primary`) to match.

### Chips
- **Style:** a 40px pill with a 1px border in 16% stone on dark and a stone label at 14px. The native input is hidden inside.
- **Selected:** ember fill and border with a white label. Focus appears as an ember outline with a 3px offset.
- **Admin chip:** a quiet `tertiary` pill with `body-ink` text, used for status and meta.

### Cards / Containers
- **Portal card:** `card-black` on obsidian with a 16px radius and a `hairline` border. When linked, the hover tints the border to 45% ember and lifts the fill to `#141414` over 0.4s.
- **Admin widget (nested shell):** an outer `surface` shell (22px, 6px padding, 7% white border) holding an `inset` panel (16px). Header icons sit in 38px dashed tiles.
- **Admin card:** an `inset` panel ringed by a drawn 6px `surface` band and a 1px border. Sub-cards step to `tertiary` with an ember border on hover.
- **Process card:** stone on sand, 16px radius, 28px padding, with a brass mono number on top.

### Inputs / Fields
- **Landing (dark contact section):** 14px/16px padding, a 10px radius, a 1px border in 16% stone over a 4% stone wash, and 16px type (no iOS zoom). Labels are 12px mono in Silver.
- **Admin:** obsidian wells with a `field-edge` border and placeholders in `#525252`.
- **Focus:** the border turns ember. The admin adds an 18% ember halo of 3px. Outlines are removed only where the border carries focus.
- **Error:** `ember-soft` border and 13px `ember-soft` message text.

### Navigation
- **Dock (landing):** a floating, blurred obsidian bar centered at the bottom. It holds the brand-mark tile (fixed 148px so typed messages never shift it), numbered section "apps" (an ember mono number above a 12px mono label; the active app gets an 8% stone wash), an ES/EN toggle, and live widgets on wide screens (clock, GitHub heatmap, Spotify now-playing, Samantha). It slides away during the intro.
- **Header (landing):** brand mark plus a greeting, absolute over the hero. It turns fixed while the hero is pinned and slides up (`translate -120%`) when hidden.
- **Admin sidebar:** a `sidebar-black` rail with no visible scrollbar. Group labels are mono eyebrows. Items are 15px/500 and the active item gets a 2px ember bar at the left edge.

### Brand Mark (signature)
`n/h_`: Cabinet Grotesk 800 at 22px with −0.02em tracking. The slash is ember and the caret is a 2px, 0.9em-tall bar blinking on `steps(1)` at 1.1s. The caret holds solid while the mark "types" messages and is static in print. The tab title animates and the favicon caret blinks while the page is hidden.

### Status Stepper (portal)
3px pill segments. Done segments are Silver, the current one is ember and upcoming ones are `#222`. Project progress reads at a glance with no labels needed.

### Samantha (signature)
The voice assistant opens from an amber-and-red film-frame intro (`#E3BC9A` / `#D93829`, panel at 75% of the viewport) and docks into a small app window with a ring that pulses with her voice. Its palette is a deliberate one-off homage and stays inside the Samantha component.

## Do's and Don'ts

### Do:
- **Do** keep one ember destination per view (The One Ember Rule) and use `ember-deep` only for hover and pressed states.
- **Do** set every button, label, number and piece of metadata in Geist Mono, and every headline and paragraph in Cabinet Grotesk.
- **Do** move emphasis up the tonal ladder (`obsidian` → `card-black`/`surface` → `inset` → `tertiary`) with 1px hairlines instead of adding shadows.
- **Do** keep nested radii concentric (22 → 16, 24 → 18 → 12).
- **Do** animate with `cubic-bezier(0.16, 1, 0.3, 1)`: 200ms for state, 0.4–0.6s for surfaces, 1.1s for hero entrances. Wrap entrances in `prefers-reduced-motion: no-preference`.
- **Do** use an ember 2px outline with a 2–3px offset for `:focus-visible` everywhere.
- **Do** reserve the dock's height at the bottom of every landing layout.
- **Do** render both languages and let `html[data-lang]` choose. Never hard-code a single-language string on a branded surface.

### Don't:
- **Don't** write the name or tagline in anything but lowercase (`noehassiel`, `artisan developer`).
- **Don't** use brass as a fill, button or background.
- **Don't** use pure `#FFFFFF` for text on dark surfaces. Use Warm Stone or `heading-ink`.
- **Don't** add drop shadows to cards. Cast shadows belong only to the dock, modals and the Samantha window.
- **Don't** ship the starter kit's Instrument Sans or neutral-800 primary on a branded surface. Scope pages under `body.landing`, `body.portal` or `body.admin` so the brand tokens apply.
- **Don't** use bouncy, elastic or spring-overshoot motion.
- **Don't** offer a light mode for the portal or admin. They are dark by commitment.
- **Don't** let the Samantha amber and red palette leak outside the Samantha component.
