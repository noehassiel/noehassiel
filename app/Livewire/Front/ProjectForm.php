<?php

namespace App\Livewire\Front;

use App\Mail\NewLeadMail;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class ProjectForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $projectType = '';

    public string $budget = '';

    public string $timeline = '';

    public string $message = '';

    /** Honeypot: real visitors never see or fill this field */
    public string $website = '';

    public string $language = 'en';

    public bool $sent = false;

    #[On('language-changed')]
    public function setLanguage(string $language): void
    {
        $this->language = in_array($language, ['en', 'es'], true) ? $language : 'en';
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'company' => ['nullable', 'string', 'max:120'],
            'projectType' => ['required', Rule::in(Lead::PROJECT_TYPES)],
            'budget' => ['required', Rule::in(Lead::BUDGETS)],
            'timeline' => ['required', Rule::in(Lead::TIMELINES)],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        $errors = fn (string $key): string => __("landing.form.errors.{$key}", locale: $this->language);

        return [
            '*.required' => $errors('required'),
            '*.in' => $errors('required'),
            'email.email' => $errors('email'),
            'message.min' => $errors('message_min'),
        ];
    }

    public function submit(): void
    {
        $key = 'project-form:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'message' => __('landing.form.errors.too_many', locale: $this->language),
            ]);
        }

        $validated = $this->validate();

        // Bots fill the honeypot; pretend it worked and store nothing
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }

        RateLimiter::hit($key, 3600);

        $lead = Lead::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company' => $validated['company'] ?: null,
            'project_type' => $validated['projectType'],
            'budget' => $validated['budget'],
            'timeline' => $validated['timeline'],
            'message' => $validated['message'],
            'language' => $this->language,
        ]);

        Mail::to(config('services.leads.email'))->queue(new NewLeadMail($lead));

        $this->reset('name', 'email', 'company', 'projectType', 'budget', 'timeline', 'message');
        $this->sent = true;
    }

    public function sendAnother(): void
    {
        $this->sent = false;
    }

    public function render(): View
    {
        return view('livewire.front.project-form', [
            'projectTypes' => Lead::PROJECT_TYPES,
            'budgets' => Lead::BUDGETS,
            'timelines' => Lead::TIMELINES,
        ]);
    }
}
