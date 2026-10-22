<?php

use App\Ai\Agents\Samantha;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Audio;
use Laravel\Ai\Prompts\AgentPrompt;

beforeEach(function () {
    $this->conversation = (string) Str::uuid();
    config(['services.os1.voice_id' => 'voice-test', 'services.os1.max_turns' => 2]);
    Audio::fake([base64_encode('fake-mp3')]);
});

it('replies with text and synthesized audio', function () {
    Samantha::fake([['reply' => 'Suena increíble. ¿Para cuándo lo necesitas?', 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Quiero una tienda en línea'])
        ->assertSuccessful()
        ->assertJson([
            'text' => 'Suena increíble. ¿Para cuándo lo necesitas?',
            'audio' => base64_encode('fake-mp3'),
            'finished' => false,
        ]);

    Samantha::assertPrompted('Quiero una tienda en línea');
    Audio::assertGenerated(fn ($prompt) => $prompt->contains('Suena increíble'));
});

it('keeps the conversation history in the session', function () {
    Samantha::fake([['reply' => 'Primera respuesta.', 'section' => 'none'], ['reply' => 'Segunda respuesta.', 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Hola']);
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Soy de Puebla'])->assertSuccessful();

    Samantha::assertPrompted(function (AgentPrompt $prompt) {
        return $prompt->contains('Soy de Puebla')
            && collect($prompt->agent->history)->pluck('content')->contains('Primera respuesta.');
    });
});

it('ends the conversation after the turn cap without calling the model', function () {
    Samantha::fake([['reply' => 'Uno.', 'section' => 'none'], ['reply' => 'Dos.', 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'a']);
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'b']);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'c'])
        ->assertSuccessful()
        ->assertJson(['finished' => true]);

    Samantha::assertPromptedTimes(2);
});

it('validates the message', function (mixed $message) {
    Samantha::fake();

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => $message])
        ->assertJsonValidationErrors('message');

    Samantha::assertNeverPrompted();
})->with([
    'missing' => [null],
    'too long' => [str_repeat('a', 501)],
]);

it('rate limits each visitor', function () {
    config(['services.os1.requests_per_minute' => 1]);
    Samantha::fake();

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'a'])->assertSuccessful();
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'b'])->assertSuccessful();
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'c'])->assertTooManyRequests();
});

it('synthesizes the greeting once and serves it from disk', function () {
    Storage::fake('local');

    $this->get(route('os1.greeting'))->assertSuccessful();
    $this->get(route('os1.greeting'))->assertSuccessful();

    Audio::assertGenerated(fn ($prompt) => $prompt->contains(Samantha::GREETINGS['en']));
    expect(Storage::disk('local')->files('os1'))->toHaveCount(1);
});

it('instructs the agent to reply in english by default', function () {
    Samantha::fake([['reply' => 'Sounds great.', 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Hello'])->assertSuccessful();

    Samantha::assertPrompted(fn (AgentPrompt $prompt) => $prompt->agent->language === 'en'
        && str_contains($prompt->agent->instructions(), 'Always reply in English')
        && $prompt->agent->history[1]['content'] === Samantha::GREETINGS['en']);
});

it('replies in spanish when the visitor selects it', function () {
    Samantha::fake([['reply' => 'Suena increíble.', 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Hola', 'language' => 'es'])->assertSuccessful();

    Samantha::assertPrompted(fn (AgentPrompt $prompt) => $prompt->agent->language === 'es'
        && str_contains($prompt->agent->instructions(), 'Always reply in Mexican Spanish'));
});

it('rejects unsupported languages', function () {
    Samantha::fake();

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Bonjour', 'language' => 'fr'])
        ->assertJsonValidationErrors('language');
});

it('says goodbye in the selected language at the turn cap', function () {
    Samantha::fake([['reply' => 'Uno.', 'section' => 'none'], ['reply' => 'Dos.', 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'a', 'language' => 'es']);
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'b', 'language' => 'es']);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'c', 'language' => 'es'])
        ->assertJson(['text' => Samantha::FAREWELLS['es'], 'finished' => true]);
});

it('caches a greeting per language', function () {
    Storage::fake('local');

    $this->get(route('os1.greeting'))->assertSuccessful();
    $this->get(route('os1.greeting', ['language' => 'es']))->assertSuccessful();
    $this->get(route('os1.greeting', ['language' => 'fr']))->assertSuccessful();

    Audio::assertGenerated(fn ($prompt) => $prompt->contains(Samantha::GREETINGS['es']));
    expect(Storage::disk('local')->files('os1'))->toHaveCount(2);
});

it('starts a fresh conversation on a new visit even after the turn cap', function () {
    Samantha::fake([['reply' => 'Uno.', 'section' => 'none'], ['reply' => 'Dos.', 'section' => 'none'], ['reply' => 'Tres.', 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'a']);
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'b']);

    $this->postJson(route('os1.reply'), ['conversation' => (string) Str::uuid(), 'message' => 'Who are you?'])
        ->assertJson(['text' => 'Tres.', 'finished' => false]);
});

it('requires a conversation id', function () {
    Samantha::fake();

    $this->postJson(route('os1.reply'), ['message' => 'Hi'])->assertJsonValidationErrors('conversation');
});

it('caps requests per visitor per day', function () {
    config(['services.os1.requests_per_day' => 2, 'services.os1.requests_per_minute' => 100]);
    Samantha::fake();

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'a'])->assertSuccessful();
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'b'])->assertSuccessful();
    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'c'])->assertTooManyRequests();
});

it('sends the visitor to a section when samantha chooses one', function () {
    Samantha::fake([['reply' => 'Let me show you the work.', 'section' => 'work']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Can I see your projects?'])
        ->assertSuccessful()
        ->assertJson(['text' => 'Let me show you the work.', 'section' => 'work', 'finished' => false]);
});

it('does not navigate when samantha answers in place', function () {
    Samantha::fake([['reply' => "I'm Samantha.", 'section' => 'none']]);

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Who are you?'])
        ->assertJson(['section' => null]);
});

it('tells samantha which sections exist', function () {
    Samantha::fake();

    $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Hi']);

    Samantha::assertPrompted(fn (AgentPrompt $prompt) => collect(array_keys(Samantha::SECTIONS))
        ->every(fn (string $key) => str_contains($prompt->agent->instructions(), "- {$key}:")));
});
