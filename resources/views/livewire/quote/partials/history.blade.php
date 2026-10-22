{{-- The back-and-forth on this proposal, newest first --}}
@if ($proposal->events->isNotEmpty())
    <section aria-labelledby="history-title">
        <h2 id="history-title" class="portal-eyebrow mb-4">Historial de la propuesta</h2>
        <ol class="flex flex-col gap-3">
            @foreach ($proposal->events as $event)
                <li class="portal-card flex gap-4 p-5">
                    <span aria-hidden="true" @class([
                        'mt-1.5 size-2 shrink-0 rounded-full',
                        'bg-[var(--ember)]' => ! $event->type->isFromClient(),
                        'bg-[var(--silver)]' => $event->type->isFromClient(),
                    ])></span>
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                            <span class="font-bold">{{ $event->type->label() }}</span>
                            <time datetime="{{ $event->created_at->toIso8601String() }}" class="font-mono text-xs text-[var(--slate)]">
                                {{ $event->created_at->translatedFormat('j \d\e F, H:i') }}
                            </time>
                        </p>
                        @if ($event->body)
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-[var(--silver)]">{{ $event->body }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
@endif
