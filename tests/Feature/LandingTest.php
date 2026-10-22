<?php

use App\Ai\Agents\Samantha;
use App\Livewire\Front\ProjectForm;
use App\Mail\NewLeadMail;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Audio;
use Livewire\Livewire;

uses(RefreshDatabase::class);

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

function fillBrief()
{
    return Livewire::test(ProjectForm::class)
        ->call('start')
        ->set('name', 'Ana López')->call('next')
        ->set('message', 'Necesitamos un sitio nuevo para nuestra cafetería en Puebla.')->call('next')
        ->call('choose', 'projectType', 'website')
        ->call('choose', 'budget', '30k_80k')
        ->call('choose', 'timeline', '1_3_months')
        ->set('email', 'ana@example.com')
        ->set('phone', '+52 222 123 4567')->call('next');
}

it('walks the brief step by step to the review', function () {
    Livewire::test(ProjectForm::class)
        ->assertSet('step', 'intro')
        ->call('start')->assertSet('step', 'name')
        ->set('name', 'Ana')->call('next')->assertSet('step', 'about')
        ->set('message', 'A website for our coffee shop in Puebla, with the menu.')->call('next')
        ->assertSet('step', 'type')
        ->call('choose', 'projectType', 'website')->assertSet('step', 'budget')
        ->call('choose', 'budget', 'unsure')->assertSet('step', 'timeline')
        ->call('choose', 'timeline', 'flexible')->assertSet('step', 'contact')
        ->set('email', 'ana@example.com')->call('next')
        ->assertSet('step', 'review')
        ->assertSee(__('landing.brief.questions.review', locale: 'en'));
});

it('does not advance past a step with an invalid answer', function () {
    Livewire::test(ProjectForm::class)
        ->call('start')
        ->call('next')
        ->assertHasErrors('name')
        ->assertSet('step', 'name');
});

it('goes back without losing answers', function () {
    Livewire::test(ProjectForm::class)
        ->call('start')
        ->set('name', 'Ana')->call('next')
        ->call('back')
        ->assertSet('step', 'name')
        ->assertSet('name', 'Ana');
});

it('lets the visitor correct answers on the review step', function () {
    fillBrief()
        ->assertSet('step', 'review')
        ->call('choose', 'budget', 'over_150k')
        ->assertSet('step', 'review')
        ->assertSet('budget', 'over_150k');
});

it('rejects unknown choice fields', function () {
    Livewire::test(ProjectForm::class)
        ->call('choose', 'email', 'x')
        ->assertStatus(422);
});

it('stores a lead with phone and emails it', function () {
    Mail::fake();

    fillBrief()
        ->call('setLanguage', 'es')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('sent', true)
        ->assertSet('step', 'intro');

    $lead = Lead::query()->sole();
    expect($lead->name)->toBe('Ana López')
        ->and($lead->phone)->toBe('+52 222 123 4567')
        ->and($lead->language)->toBe('es');

    Mail::assertQueued(NewLeadMail::class, fn (NewLeadMail $mail) => $mail->hasTo('hello@noehassiel.test') && $mail->lead->is($lead));
});

it('validates contact details in the visitor language', function () {
    Livewire::test(ProjectForm::class)
        ->call('setLanguage', 'es')
        ->set('step', 'contact')
        ->set('email', 'not-an-email')
        ->set('phone', 'call me maybe')
        ->call('next')
        ->assertHasErrors(['email', 'phone'])
        ->assertSee(__('landing.form.errors.email', locale: 'es'))
        ->assertSet('step', 'contact');
});

it('silently drops honeypot submissions', function () {
    Mail::fake();

    fillBrief()->set('website', 'https://spam.example')->call('submit')->assertSet('sent', true);

    expect(Lead::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rate limits brief submissions per visitor', function () {
    Mail::fake();

    fillBrief()->call('submit');
    fillBrief()->call('submit');
    fillBrief()->call('submit');
    fillBrief()->call('submit')->assertHasErrors('message');

    expect(Lead::query()->count())->toBe(3);
});

it('serves samantha\'s brief questions as cached audio', function () {
    Storage::fake('local');
    Audio::fake([base64_encode('fake-mp3')]);

    $this->get(route('os1.line', ['key' => 'name', 'language' => 'es']))->assertSuccessful();
    $this->get(route('os1.line', ['key' => 'name', 'language' => 'es']))->assertSuccessful();
    $this->get(route('os1.line', ['key' => 'nope']))->assertNotFound();

    Audio::assertGenerated(fn ($prompt) => $prompt->contains('¿Cómo te llamas?'));
    expect(Storage::disk('local')->files('os1'))->toHaveCount(1);
});
