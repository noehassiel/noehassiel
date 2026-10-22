<div class="space-y-12">
    <header>
        <h1 class="text-4xl font-extrabold tracking-tight">{{ __('portal.team.title') }}</h1>
        <p class="mt-3 max-w-2xl text-[var(--silver)]">{{ __('portal.team.intro') }}</p>
    </header>

    <div class="grid gap-10 lg:grid-cols-5">
        {{-- Members and pending invitations --}}
        <section class="min-w-0 space-y-4 lg:col-span-3" aria-labelledby="members-heading">
            <h2 id="members-heading" class="portal-eyebrow">{{ __('portal.team.members') }}</h2>

            <ul class="space-y-3">
                @foreach ($members as $member)
                    <li wire:key="member-{{ $member->id }}" class="portal-card p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold">
                                    {{ $member->name }}
                                    @if ($member->id === auth()->id())
                                        <span class="font-normal text-[var(--slate)]">· {{ __('portal.team.you') }}</span>
                                    @endif
                                </p>
                                <p class="truncate font-mono text-xs text-[var(--slate)]">{{ $member->email }}</p>
                                <p class="mt-2 text-sm text-[var(--silver)]">
                                    @if ($member->isPortalOwner())
                                        {{ __('portal.team.owner_access') }}
                                    @else
                                        {{ $member->can_view_all_projects ? __('portal.team.all_projects') : trans_choice('portal.team.n_projects', $member->projects->count()) }}
                                        · {{ $member->can_view_invoices ? __('portal.team.sees_invoices') : __('portal.team.no_invoices') }}
                                    @endif
                                </p>
                            </div>

                            @if ($member->isPortalOwner())
                                <span class="portal-eyebrow">{{ __('portal.team.owner') }}</span>
                            @elseif ($editingUserId !== $member->id)
                                <div class="flex gap-4">
                                    <button type="button" wire:click="editMember({{ $member->id }})" class="cursor-pointer font-mono text-xs uppercase tracking-[0.14em] text-[var(--ember)]">{{ __('portal.team.edit') }}</button>
                                    <button type="button" wire:click="removeMember({{ $member->id }})" wire:confirm="{{ __('portal.team.remove_confirm', ['name' => $member->name]) }}"
                                        class="cursor-pointer font-mono text-xs uppercase tracking-[0.14em] text-[var(--slate)] hover:text-[var(--stone)]">{{ __('portal.team.remove') }}</button>
                                </div>
                            @endif
                        </div>

                        @if ($editingUserId === $member->id)
                            <form wire:submit="saveMember" class="mt-5 space-y-5 border-t border-[#1C1C1C] pt-5">
                                @include('livewire.portal.partials.team-access', ['all' => 'editAllProjects', 'ids' => 'editProjectIds', 'invoices' => 'editViewInvoices'])
                                <div class="flex gap-3">
                                    <button type="submit" class="cursor-pointer rounded-xl bg-[var(--ember)] px-5 py-2.5 font-bold text-[var(--obsidian)]">{{ __('portal.team.save') }}</button>
                                    <button type="button" wire:click="cancelEdit" class="cursor-pointer px-3 text-sm text-[var(--silver)] hover:text-[var(--stone)]">{{ __('portal.team.cancel') }}</button>
                                </div>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($invitations->isNotEmpty())
                <h2 class="portal-eyebrow pt-4">{{ __('portal.team.pending') }}</h2>
                <ul class="space-y-3">
                    @foreach ($invitations as $invitation)
                        <li wire:key="invitation-{{ $invitation->id }}" class="portal-card flex flex-wrap items-center justify-between gap-3 p-5">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-sm">{{ $invitation->email }}</p>
                                <p class="mt-1 text-xs text-[var(--slate)]">{{ __('portal.team.expires', ['date' => $invitation->expires_at->translatedFormat('j M')]) }}</p>
                            </div>
                            <button type="button" wire:click="cancelInvitation({{ $invitation->id }})"
                                class="cursor-pointer font-mono text-xs uppercase tracking-[0.14em] text-[var(--slate)] hover:text-[var(--stone)]">{{ __('portal.team.cancel_invitation') }}</button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Invite --}}
        <section class="min-w-0 lg:col-span-2" aria-labelledby="invite-heading">
            <form wire:submit="invite" class="portal-card space-y-5 p-6">
                <h2 id="invite-heading" class="portal-eyebrow">{{ __('portal.team.invite') }}</h2>

                <div class="flex flex-col gap-2">
                    <label for="invite-email" class="text-sm font-bold">{{ __('portal.team.email') }}</label>
                    <input id="invite-email" wire:model="email" type="email" required autocomplete="off"
                        @error('email') aria-invalid="true" aria-describedby="invite-email-error" @enderror
                        class="w-full min-w-0 rounded-xl border border-[#2A2A2A] bg-[#0D0D0D] px-4 py-3 text-[var(--stone)]">
                    @error('email')
                        <p id="invite-email-error" role="alert" class="text-sm text-[var(--ember)]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="invite-locale" class="text-sm font-bold">{{ __('portal.team.language') }}</label>
                    <select id="invite-locale" wire:model="locale" class="w-full rounded-xl border border-[#2A2A2A] bg-[#0D0D0D] px-4 py-3 text-[var(--stone)]">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </select>
                </div>

                @include('livewire.portal.partials.team-access', ['all' => 'allProjects', 'ids' => 'projectIds', 'invoices' => 'viewInvoices'])

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full cursor-pointer rounded-xl bg-[var(--ember)] px-5 py-3 font-bold text-[var(--obsidian)] transition-opacity hover:opacity-90 disabled:opacity-60">
                    {{ __('portal.team.send_invite') }}
                </button>
            </form>
        </section>
    </div>
</div>
