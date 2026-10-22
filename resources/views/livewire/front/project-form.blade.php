@php
    $choiceSteps = ['type' => 'projectType', 'budget' => 'budget', 'timeline' => 'timeline'];
    $choiceLabels = ['projectType' => 'form.project_type', 'budget' => 'form.budget', 'timeline' => 'form.timeline'];
    $mic = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/></svg>';
    $stepNumber = array_search($step, $questionSteps, true);
@endphp

<div class="brief" data-brief>
    @if ($sent)
        <div class="brief-card" role="status">
            <p class="brief-title"><x-t key="form.success_title" /></p>
            <p class="brief-body"><x-t key="form.success_body" /></p>
            <button type="button" wire:click="sendAnother" class="button button-ghost">
                <x-t key="form.another" />
            </button>
        </div>
    @elseif ($step === 'intro')
        <div class="brief-card brief-intro">
            <span class="brief-ring" aria-hidden="true"></span>
            <p class="brief-title"><x-t key="brief.intro_title" /></p>
            <p class="brief-body"><x-t key="brief.intro_body" /></p>
            <button type="button" wire:click="start" class="button button-primary">
                <x-t key="brief.start" />
            </button>
        </div>
    @else
        <div class="brief-card" wire:key="brief-step-{{ $step }}"
            x-data x-init="$nextTick(() => window.brief?.ask('{{ $step }}'))">

            <div class="brief-top">
                @if ($stepNumber !== false)
                    <p class="brief-progress mono">
                        <x-t key="brief.step" /> {{ $stepNumber + 1 }} <x-t key="brief.of" /> {{ count($questionSteps) }}
                    </p>
                @else
                    <p class="brief-progress mono"><x-t key="brief.edit_hint" /></p>
                @endif

                <button type="button" class="brief-mute" data-brief-mute aria-pressed="false">
                    <span class="brief-mute-on"><x-t key="brief.mute" class="sr-only" /><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H3v6h3l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M18.5 5.5a9 9 0 0 1 0 13"/></svg></span>
                    <span class="brief-mute-off"><x-t key="brief.unmute" class="sr-only" /><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H3v6h3l5 4V5z"/><path d="m16 9 5 6M21 9l-5 6"/></svg></span>
                </button>
            </div>

            <div class="brief-question">
                <span class="brief-ring" aria-hidden="true"></span>
                <p class="brief-question-text" aria-live="polite"><x-t key="brief.questions.{{ $step }}" /></p>
            </div>

            <form wire:submit="{{ $step === 'review' ? 'submit' : 'next' }}" novalidate class="brief-form">
                {{-- Name: voice or typing --}}
                @if (in_array($step, ['name', 'review']))
                    <div class="field">
                        @if ($step === 'review')
                            <label for="brief-name"><x-t key="form.name" /></label>
                        @endif
                        <div class="field-voice">
                            <input id="brief-name" type="text" wire:model="name" autocomplete="name"
                                @if ($step === 'name') autofocus aria-label="{{ __('landing.form.name', locale: 'en') }}" @endif
                                @error('name') aria-invalid="true" @enderror>
                            <button type="button" class="voice-button" data-brief-speak="brief-name">
                                <x-t key="brief.speak" class="sr-only" />{!! $mic !!}
                            </button>
                        </div>
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Project description: voice or typing, appends what is heard --}}
                @if (in_array($step, ['about', 'review']))
                    <div class="field">
                        @if ($step === 'review')
                            <label for="brief-message"><x-t key="form.message" /></label>
                        @endif
                        <div class="field-voice field-voice-area">
                            <textarea id="brief-message" wire:model="message" rows="{{ $step === 'review' ? 4 : 5 }}"
                                @if ($step === 'about') aria-label="{{ __('landing.form.message', locale: 'en') }}" @endif
                                @error('message') aria-invalid="true" @enderror></textarea>
                            <button type="button" class="voice-button" data-brief-speak="brief-message" data-brief-append>
                                <x-t key="brief.speak" class="sr-only" />{!! $mic !!}
                            </button>
                        </div>
                        @error('message') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Multiple choice: tap to answer --}}
                @foreach ($choiceSteps as $choiceStep => $field)
                    @if ($step === $choiceStep || $step === 'review')
                        <fieldset class="field">
                            @if ($step === 'review')
                                <legend><x-t :key="$choiceLabels[$field]" /></legend>
                            @endif
                            <div class="chips">
                                @foreach ($options[$field][1] as $value)
                                    <button type="button" class="chip" wire:key="{{ $field }}-{{ $value }}"
                                        wire:click="choose('{{ $field }}', '{{ $value }}')"
                                        aria-pressed="{{ $this->{$field} === $value ? 'true' : 'false' }}">
                                        <x-t :key="$options[$field][0].'.'.$value" />
                                    </button>
                                @endforeach
                            </div>
                            @error($field) <p class="field-error">{{ $message }}</p> @enderror
                        </fieldset>
                    @endif
                @endforeach

                {{-- Contact: typed --}}
                @if (in_array($step, ['contact', 'review']))
                    <div class="brief-grid">
                        <div class="field">
                            <label for="brief-email"><x-t key="form.email" /></label>
                            <input id="brief-email" type="email" wire:model="email" autocomplete="email" spellcheck="false"
                                @error('email') aria-invalid="true" @enderror>
                            @error('email') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field">
                            <label for="brief-phone"><x-t key="brief.phone" /></label>
                            <input id="brief-phone" type="tel" wire:model="phone" autocomplete="tel"
                                @error('phone') aria-invalid="true" @enderror>
                            @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="field field-wide">
                            <label for="brief-company"><x-t key="form.company" /></label>
                            <input id="brief-company" type="text" wire:model="company" autocomplete="organization">
                        </div>
                    </div>
                @endif

                <div class="field-honeypot" aria-hidden="true">
                    <label for="brief-website">Website</label>
                    <input id="brief-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="brief-actions">
                    <button type="button" wire:click="back" class="button button-ghost">
                        <x-t key="brief.back" />
                    </button>

                    @if ($step === 'review')
                        <button type="submit" class="button button-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submit"><x-t key="form.submit" /></span>
                            <span wire:loading wire:target="submit"><x-t key="form.sending" /></span>
                        </button>
                    @elseif (! array_key_exists($step, $choiceSteps))
                        <button type="submit" class="button button-primary">
                            <x-t key="brief.next" />
                        </button>
                    @endif
                </div>
            </form>
        </div>
    @endif
</div>
