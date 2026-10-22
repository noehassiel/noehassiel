{{-- Confirms the client's latest request reached us, with what they asked and when --}}
@php($request = $proposal->events->firstWhere('type', \App\Enums\ProposalEventType::ChangesRequested))
<div role="status" class="portal-card p-5">
    <p class="flex flex-wrap items-baseline justify-between gap-x-4">
        <span class="font-bold">Recibí tus comentarios.</span>
        @if ($request)
            <time datetime="{{ $request->created_at->toIso8601String() }}" class="font-mono text-xs text-[var(--slate)]">{{ $request->created_at->translatedFormat('j \d\e F, H:i') }}</time>
        @endif
    </p>
    @if ($request?->body)
        <blockquote class="mt-3 border-l-2 border-[var(--brass)] pl-4 text-sm whitespace-pre-line text-[var(--silver)]">{{ $request->body }}</blockquote>
    @endif
    <p class="mt-3 text-sm text-[var(--silver)]">Estoy ajustando la propuesta. Cuando esté lista la vas a ver aquí mismo, con el mismo link. Si se te ocurre algo más, mándamelo abajo.</p>
</div>
