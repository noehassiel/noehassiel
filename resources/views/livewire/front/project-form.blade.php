<div class="project-form">
    @if ($sent)
        <div class="project-form-success" role="status">
            <p class="project-form-success-title"><x-t key="form.success_title" /></p>
            <p class="project-form-success-body"><x-t key="form.success_body" /></p>
            <button type="button" wire:click="sendAnother" class="button button-ghost">
                <x-t key="form.another" />
            </button>
        </div>
    @else
        <form wire:submit="submit" novalidate class="project-form-grid">
            <div class="field">
                <label for="lead-name"><x-t key="form.name" /></label>
                <input id="lead-name" type="text" wire:model="name" autocomplete="name" required
                    @error('name') aria-invalid="true" aria-describedby="lead-name-error" @enderror>
                @error('name') <p class="field-error" id="lead-name-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="lead-email"><x-t key="form.email" /></label>
                <input id="lead-email" type="email" wire:model="email" autocomplete="email" required spellcheck="false"
                    @error('email') aria-invalid="true" aria-describedby="lead-email-error" @enderror>
                @error('email') <p class="field-error" id="lead-email-error">{{ $message }}</p> @enderror
            </div>

            <div class="field field-wide">
                <label for="lead-company"><x-t key="form.company" /></label>
                <input id="lead-company" type="text" wire:model="company" autocomplete="organization">
            </div>

            @foreach ([
                'projectType' => ['form.project_type', 'form.types', $projectTypes],
                'budget' => ['form.budget', 'form.budgets', $budgets],
                'timeline' => ['form.timeline', 'form.timelines', $timelines],
            ] as $field => [$label, $options, $values])
                <fieldset class="field field-wide" @error($field) aria-invalid="true" @enderror>
                    <legend><x-t :key="$label" /></legend>
                    <div class="chips">
                        @foreach ($values as $value)
                            <label class="chip" wire:key="{{ $field }}-{{ $value }}">
                                <input type="radio" wire:model="{{ $field }}" value="{{ $value }}" name="{{ $field }}">
                                <x-t :key="$options.'.'.$value" />
                            </label>
                        @endforeach
                    </div>
                    @error($field) <p class="field-error">{{ $message }}</p> @enderror
                </fieldset>
            @endforeach

            <div class="field field-wide">
                <label for="lead-message"><x-t key="form.message" /></label>
                <textarea id="lead-message" wire:model="message" rows="5" required aria-describedby="lead-message-hint"
                    @error('message') aria-invalid="true" @enderror></textarea>
                <p class="field-hint" id="lead-message-hint"><x-t key="form.message_hint" /></p>
                @error('message') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field-honeypot" aria-hidden="true">
                <label for="lead-website">Website</label>
                <input id="lead-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="field-wide">
                <button type="submit" class="button button-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit"><x-t key="form.submit" /></span>
                    <span wire:loading wire:target="submit"><x-t key="form.sending" /></span>
                </button>
            </div>
        </form>
    @endif
</div>
