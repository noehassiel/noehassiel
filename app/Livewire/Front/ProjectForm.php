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

/**
 * The project brief, guided by Samantha one question at a time:
 * open answers by voice or typing, multiple choice by tapping, contact details typed,
 * and a final review where every answer can be corrected before sending.
 */
class ProjectForm extends Component
{
    public const STEPS = ['intro', 'name', 'about', 'type', 'budget', 'timeline', 'contact', 'review'];

    /**
     * Fields each step is responsible for, validated before moving on.
     *
     * @var array<string, list<string>>
     */
    private const STEP_FIELDS = [
        'name' => ['name'],
        'about' => ['message'],
        'type' => ['projectType'],
        'budget' => ['budget'],
        'timeline' => ['timeline'],
        'contact' => ['email', 'phone', 'company'],
    ];

    public string $step = 'intro';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

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
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s\-().]{7,20}$/'],
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
            'phone.regex' => $errors('phone'),
            'message.min' => $errors('message_min'),
        ];
    }

    public function start(): void
    {
        $this->step = 'name';
    }

    public function next(): void
    {
        $this->validate(array_intersect_key($this->rules(), array_flip(self::STEP_FIELDS[$this->step] ?? [])));

        $this->step = self::STEPS[array_search($this->step, self::STEPS, true) + 1] ?? 'review';
    }

    public function back(): void
    {
        $this->resetValidation();
        $this->step = self::STEPS[max(0, array_search($this->step, self::STEPS, true) - 1)];
    }

    /**
     * Multiple-choice answers advance on tap.
     */
    public function choose(string $field, string $value): void
    {
        abort_unless(in_array($field, ['projectType', 'budget', 'timeline'], true), 422);

        $this->{$field} = $value;

        if ($this->step !== 'review') {
            $this->next();
        }
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
            'phone' => $validated['phone'] ?: null,
            'company' => $validated['company'] ?: null,
            'project_type' => $validated['projectType'],
            'budget' => $validated['budget'],
            'timeline' => $validated['timeline'],
            'message' => $validated['message'],
            'language' => $this->language,
        ]);

        Mail::to(config('services.leads.email'))->queue(new NewLeadMail($lead));

        $this->reset('name', 'email', 'phone', 'company', 'projectType', 'budget', 'timeline', 'message');
        $this->step = 'intro';
        $this->sent = true;
    }

    public function sendAnother(): void
    {
        $this->sent = false;
    }

    public function render(): View
    {
        return view('livewire.front.project-form', [
            'options' => [
                'projectType' => ['form.types', Lead::PROJECT_TYPES],
                'budget' => ['form.budgets', Lead::BUDGETS],
                'timeline' => ['form.timelines', Lead::TIMELINES],
            ],
            'questionSteps' => array_slice(self::STEPS, 1, -1),
        ]);
    }
}
