<?php

use App\Ai\Agents\Samantha;
use App\Livewire\Front\ProjectForm;
use App\Mail\NewLeadMail;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config(['services.leads.email' => 'hello@noehassiel.test']);
    RateLimiter::clear('project-form:127.0.0.1');
});

it('renders every landing section in both languages', function () {
    $response = $this->get('/')->assertSuccessful();

    foreach (['work', 'services', 'process', 'about', 'contact'] as $section) {
        $response->assertSee('id="'.$section.'"', false);
        $response->assertSee('data-dock-link="'.$section.'"', false);
    }

    $response->assertSee(__('landing.hero.title_2', locale: 'en'))
        ->assertSee(__('landing.hero.title_2', locale: 'es'))
        ->assertSeeLivewire(ProjectForm::class);
});

it('renders the dock with samantha as an app', function () {
    $this->get('/')
        ->assertSee('id="dock"', false)
        ->assertSee('id="os1-toggle"', false)
        ->assertSee('aria-controls="os1-frame"', false);
});

it('keeps sections in sync with what samantha can navigate to', function () {
    $html = $this->get('/')->getContent();

    foreach (array_keys(Samantha::SECTIONS) as $section) {
        expect($html)->toContain('id="'.$section.'"');
    }
});

it('stores a lead and emails it', function () {
    Mail::fake();

    Livewire::test(ProjectForm::class)
        ->set('name', 'Ana López')
        ->set('email', 'ana@example.com')
        ->set('projectType', 'website')
        ->set('budget', '30k_80k')
        ->set('timeline', '1_3_months')
        ->set('message', 'Necesitamos un sitio nuevo para nuestra cafetería en Puebla.')
        ->call('setLanguage', 'es')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    $lead = Lead::query()->sole();
    expect($lead->name)->toBe('Ana López')->and($lead->language)->toBe('es');

    Mail::assertQueued(NewLeadMail::class, fn (NewLeadMail $mail) => $mail->hasTo('hello@noehassiel.test') && $mail->lead->is($lead));
});

it('validates the brief with messages in the visitor language', function () {
    Livewire::test(ProjectForm::class)
        ->call('setLanguage', 'es')
        ->set('email', 'not-an-email')
        ->set('budget', 'a-million')
        ->set('message', 'Hola')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'projectType', 'budget', 'timeline', 'message'])
        ->assertSee(__('landing.form.errors.email', locale: 'es'));

    expect(Lead::query()->count())->toBe(0);
});

it('silently drops honeypot submissions', function () {
    Mail::fake();

    Livewire::test(ProjectForm::class)
        ->set('name', 'Bot')
        ->set('email', 'bot@example.com')
        ->set('projectType', 'website')
        ->set('budget', 'unsure')
        ->set('timeline', 'asap')
        ->set('message', 'Buy cheap backlinks for your website today.')
        ->set('website', 'https://spam.example')
        ->call('submit')
        ->assertSet('sent', true);

    expect(Lead::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rate limits brief submissions per visitor', function () {
    Mail::fake();

    $submit = fn () => Livewire::test(ProjectForm::class)
        ->set('name', 'Ana')
        ->set('email', 'ana@example.com')
        ->set('projectType', 'web_app')
        ->set('budget', 'over_150k')
        ->set('timeline', 'flexible')
        ->set('message', 'A long enough description of the project we have in mind.')
        ->call('submit');

    $submit();
    $submit();
    $submit();
    $submit()->assertHasErrors('message');

    expect(Lead::query()->count())->toBe(3);
});
