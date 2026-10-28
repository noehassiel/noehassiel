@php
    use App\Enums\TicketStatus;

    $statusStyles = [
        TicketStatus::Open->value => 'text-[var(--silver)]',
        TicketStatus::InProgress->value => 'text-[var(--brass)]',
        TicketStatus::Resolved->value => 'text-[var(--ember)]',
        TicketStatus::Closed->value => 'text-[var(--slate)]',
    ];
@endphp

{{-- Herd-style review shell: the client's site framed full-screen, a chat panel on the left, the nh/ button to open it --}}
<div
    x-data="{
        open: (() => { try { return localStorage.getItem('nh-preview-panel') !== 'closed' } catch (e) { return true } })(),
        tab: 'chat',
        device: 'desktop',
        path: '/',
        origin: @js($previewOrigin),
        base: @js(rtrim($project->preview_url, '/')),
        toggle() {
            this.open = ! this.open
            try { localStorage.setItem('nh-preview-panel', this.open ? 'open' : 'closed') } catch (e) {}
        },
        reload() { this.$refs.frame.src = this.base + this.path },
        // The preview bridge (noehassiel/preview-bridge) posts the path on every navigation
        onMessage(event) {
            if (event.origin !== this.origin || event.data?.type !== 'nh:navigate') return
            this.path = String(event.data.path || '/')
            $wire.pagePath = this.path
        },
    }"
    @message.window="onMessage($event)"
    @keydown.escape.window="open && toggle()"
    class="relative z-10 flex h-svh gap-2 p-2"
>
    {{-- Chat panel --}}
    <aside x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-out duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-x-4"
        class="fixed inset-2 z-20 flex flex-col overflow-hidden rounded-2xl border border-[#1C1C1C] bg-[#111111] sm:static sm:inset-auto sm:w-[380px] sm:shrink-0"
        aria-label="{{ __('portal.preview.panel') }}">

        <header class="flex items-center justify-between gap-3 px-4 pt-4">
            <a href="{{ $backUrl }}" wire:navigate class="flex min-w-0 items-center gap-2.5">
                <img src="{{ asset('logo/isotipo_dark_ember.svg') }}" alt="noehassiel" class="size-6 shrink-0">
                <span class="truncate text-sm font-bold">{{ $project->name }}</span>
            </a>
            <button type="button" @click="toggle()" class="cursor-pointer rounded-lg p-1.5 text-[var(--slate)] transition-colors hover:bg-[#1C1C1C] hover:text-[var(--stone)]" aria-label="{{ __('portal.preview.close') }}">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2" /><path d="M9 4v16" /></svg>
            </button>
        </header>

        <div class="flex items-center gap-2 px-4 pt-3" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'chat'" @click="tab = 'chat'"
                :class="tab === 'chat' ? 'bg-[#1C1C1C] text-[var(--stone)]' : 'text-[var(--slate)] hover:text-[var(--stone)]'"
                class="cursor-pointer rounded-lg px-2.5 py-1 text-xs font-medium transition-colors">{{ __('portal.preview.chat') }}</button>
            <button type="button" role="tab" :aria-selected="tab === 'changes'" @click="tab = 'changes'"
                :class="tab === 'changes' ? 'bg-[#1C1C1C] text-[var(--stone)]' : 'text-[var(--slate)] hover:text-[var(--stone)]'"
                class="cursor-pointer rounded-lg px-2.5 py-1 text-xs font-medium transition-colors">
                {{ __('portal.preview.changes') }} <span class="font-mono text-[var(--slate)]">{{ $tickets->count() }}</span>
            </button>
            <span @class([
                'ml-auto rounded-full border px-2 py-0.5 font-mono text-[10px] uppercase tracking-[0.12em]',
                'border-[#2A2A2A] text-[var(--silver)]' => $project->hasRoundsLeft(),
                'border-[color-mix(in_srgb,var(--brass)_50%,transparent)] text-[var(--brass)]' => ! $project->hasRoundsLeft(),
            ])>
                {{ $project->hasRoundsLeft()
                    ? __('portal.preview.round', ['current' => $project->current_round, 'total' => $project->revision_rounds])
                    : __('portal.preview.rounds_used') }}
            </span>
        </div>

        {{-- Chat --}}
        <div x-show="tab === 'chat'" class="flex min-h-0 flex-1 flex-col">
            <div x-data x-init="$el.scrollTop = $el.scrollHeight; new MutationObserver(() => $el.scrollTop = $el.scrollHeight).observe($el, { childList: true, subtree: true })"
                class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-5" aria-live="polite">
                @if ($messages->isEmpty())
                    <div class="space-y-3 text-sm leading-relaxed text-[var(--silver)]">
                        <p class="portal-eyebrow text-[var(--ember)]">noehassiel</p>
                        <p>{{ __('portal.preview.intro') }}</p>
                        <p>{{ __('portal.preview.intro_scope') }}</p>
                    </div>
                @endif

                @foreach ($messages as $entry)
                    @if ($entry->isFromClient())
                        <div wire:key="message-{{ $entry->id }}" class="flex flex-col items-end gap-1">
                            @if ($entry->user_id !== auth()->id())
                                <p class="font-mono text-[10px] text-[var(--slate)]">{{ $entry->user?->name }}</p>
                            @endif
                            <p class="max-w-[85%] whitespace-pre-line rounded-2xl bg-[#1C1C1C] px-3.5 py-2 text-sm">{{ $entry->content }}</p>
                        </div>
                    @else
                        <div wire:key="message-{{ $entry->id }}" class="space-y-1.5">
                            <p class="portal-eyebrow">noehassiel</p>
                            <p class="whitespace-pre-line text-sm leading-relaxed">{{ $entry->content }}</p>
                        </div>
                    @endif
                @endforeach

                <div wire:loading.block wire:target="send" class="hidden space-y-1.5">
                    <p class="portal-eyebrow">noehassiel</p>
                    <p class="text-sm text-[var(--slate)]">{{ __('portal.preview.thinking') }}</p>
                </div>

                @if ($pendingQuote && $canRequestChanges)
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="confirmQuote" class="cursor-pointer rounded-lg bg-[var(--ember)] px-3 py-1.5 text-xs font-bold text-[var(--obsidian)] transition-opacity hover:opacity-90">{{ __('portal.preview.quote_yes') }}</button>
                        <button type="button" wire:click="declineQuote" class="cursor-pointer rounded-lg border border-[#2A2A2A] px-3 py-1.5 text-xs text-[var(--silver)] transition-colors hover:border-[var(--silver)] hover:text-[var(--stone)]">{{ __('portal.preview.quote_no') }}</button>
                    </div>
                @endif
            </div>

            @if ($canRequestChanges)
                <form wire:submit="send" class="p-3">
                    <div class="rounded-2xl border border-[#2A2A2A] bg-[#0D0D0D] transition-colors focus-within:border-[var(--slate)]">
                        <label for="preview-message" class="sr-only">{{ __('portal.preview.message') }}</label>
                        <textarea id="preview-message" wire:model="message" rows="3"
                            @keydown.enter.prevent="if (! $event.shiftKey) $wire.send()"
                            placeholder="{{ __('portal.preview.placeholder') }}"
                            class="block w-full resize-none bg-transparent px-3.5 pt-3 text-sm placeholder:text-[var(--slate)] focus:outline-none"></textarea>
                        <div class="flex items-center justify-between gap-2 px-3 pb-3">
                            <span class="truncate font-mono text-[10px] text-[var(--slate)]" x-text="path"></span>
                            <button type="submit" wire:loading.attr="disabled" wire:target="send"
                                class="grid size-8 shrink-0 cursor-pointer place-items-center rounded-lg bg-[#1C1C1C] text-[var(--stone)] transition-colors hover:bg-[var(--ember)] hover:text-[var(--obsidian)] disabled:opacity-50"
                                aria-label="{{ __('portal.preview.send') }}">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7" /></svg>
                            </button>
                        </div>
                    </div>
                    @error('message') <p class="mt-2 text-xs text-[var(--ember)]">{{ $message }}</p> @enderror
                </form>
            @endif
        </div>

        {{-- Requested changes, mirrored from Linear --}}
        <ul x-show="tab === 'changes'" x-cloak class="min-h-0 flex-1 space-y-2 overflow-y-auto px-4 py-5">
            @forelse ($tickets as $ticket)
                <li wire:key="ticket-{{ $ticket->id }}" class="rounded-xl border border-[#1C1C1C] p-3">
                    <p class="text-sm font-medium">{{ $ticket->title }}</p>
                    <p class="mt-1 flex flex-wrap gap-x-2 font-mono text-[10px] uppercase tracking-[0.12em]">
                        <span class="{{ $statusStyles[$ticket->status->value] }}">{{ $ticket->status->label() }}</span>
                        <span class="text-[var(--slate)]">{{ $ticket->category->label() }}</span>
                        @if ($ticket->in_scope === false)
                            <span class="text-[var(--brass)]">{{ __('portal.preview.to_quote') }}</span>
                        @endif
                    </p>
                </li>
            @empty
                <li class="text-sm text-[var(--silver)]">{{ __('portal.preview.no_changes') }}</li>
            @endforelse
        </ul>

        @if ($canApprove)
            <div class="border-t border-[#1C1C1C] p-3">
                <button type="button" wire:click="approve"
                    wire:confirm="{{ __('portal.preview.approve_confirm') }}"
                    class="w-full cursor-pointer rounded-xl bg-[var(--ember)] px-4 py-2.5 text-sm font-bold text-[var(--obsidian)] transition-opacity hover:opacity-90">
                    {{ __('portal.preview.approve') }}
                </button>
            </div>
        @elseif ($project->approved_at)
            <p class="border-t border-[#1C1C1C] p-4 text-sm text-[var(--brass)]">{{ __('portal.preview.approved', ['date' => $project->approved_at->translatedFormat('j M Y')]) }}</p>
        @endif
    </aside>

    {{-- Preview --}}
    <main class="flex min-w-0 flex-1 flex-col gap-2">
        <div class="flex items-center gap-1.5">
            <a href="{{ $backUrl }}" wire:navigate
                class="rounded-lg p-2 text-[var(--slate)] transition-colors hover:bg-[#1C1C1C] hover:text-[var(--stone)]" aria-label="{{ __('portal.project.back') }}">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 6l-6 6 6 6" /></svg>
            </a>
            <button type="button" @click="reload()" class="cursor-pointer rounded-lg p-2 text-[var(--slate)] transition-colors hover:bg-[#1C1C1C] hover:text-[var(--stone)]" aria-label="{{ __('portal.preview.reload') }}">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.34-5.66M20 4v4h-4" /></svg>
            </button>
            <div class="flex min-w-0 flex-1 items-center gap-2 rounded-lg border border-[#1C1C1C] bg-[#111111] px-3 py-1.5">
                <svg class="size-3.5 shrink-0 text-[var(--slate)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3c2.5 3 2.5 15 0 18M12 3c-2.5 3-2.5 15 0 18" /></svg>
                <span class="truncate font-mono text-xs text-[var(--silver)]" x-text="path"></span>
            </div>
            <div class="hidden items-center rounded-lg border border-[#1C1C1C] p-0.5 sm:flex" role="group" aria-label="{{ __('portal.preview.device') }}">
                <button type="button" @click="device = 'desktop'" :aria-pressed="device === 'desktop'" :class="device === 'desktop' ? 'bg-[#1C1C1C] text-[var(--stone)]' : 'text-[var(--slate)]'" class="cursor-pointer rounded-md p-1.5 transition-colors" aria-label="{{ __('portal.preview.desktop') }}">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="1.5" /><path d="M8 20h8M12 16v4" /></svg>
                </button>
                <button type="button" @click="device = 'mobile'" :aria-pressed="device === 'mobile'" :class="device === 'mobile' ? 'bg-[#1C1C1C] text-[var(--stone)]' : 'text-[var(--slate)]'" class="cursor-pointer rounded-md p-1.5 transition-colors" aria-label="{{ __('portal.preview.mobile') }}">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="2" /><path d="M11 18h2" /></svg>
                </button>
            </div>
            <a :href="base + path" target="_blank" rel="noopener" class="rounded-lg p-2 text-[var(--slate)] transition-colors hover:bg-[#1C1C1C] hover:text-[var(--stone)]" aria-label="{{ __('portal.preview.new_tab') }}">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" /></svg>
            </a>
        </div>

        <div class="flex min-h-0 flex-1 justify-center">
            <iframe x-ref="frame" src="{{ $project->preview_url }}" title="{{ __('portal.preview.frame', ['project' => $project->name]) }}"
                :class="device === 'mobile' ? 'max-w-[390px]' : 'max-w-none'"
                class="h-full w-full rounded-xl border border-[#1C1C1C] bg-white transition-[max-width] duration-500 ease-out"></iframe>
        </div>
    </main>

    {{-- nh/ button: opens the panel --}}
    <button type="button" x-show="! open" x-cloak @click="toggle()"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed bottom-5 left-5 z-30 flex cursor-pointer items-center gap-2.5 rounded-full border border-[#2A2A2A] bg-[#111111]/95 py-2 pr-4 pl-2 shadow-2xl backdrop-blur transition-colors hover:border-[var(--ember)]">
        <img src="{{ asset('logo/isotipo_dark_ember.svg') }}" alt="" class="size-7">
        <span class="text-sm font-bold">{{ __('portal.preview.open') }}</span>
    </button>
</div>
