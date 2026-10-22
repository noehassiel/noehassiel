<?php

use App\Ai\Agents\Samantha;
use App\Http\Controllers\Os1Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
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
            'finished' => false,
        ]);

    Samantha::assertPrompted('Quiero una tienda en línea');
    Audio::assertNothingGenerated();
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

it('returns a streaming audio url that only speaks the reply it was made for', function () {
    Samantha::fake([['reply' => 'Hola, soy Samantha.', 'section' => 'none']]);
    config(['ai.providers.eleven.key' => 'test-key']);
    Http::fake(['api.elevenlabs.io/*' => Http::response('mp3-bytes', 200, ['Content-Type' => 'audio/mpeg'])]);

    $url = $this->postJson(route('os1.reply'), ['conversation' => $this->conversation, 'message' => 'Hola', 'language' => 'es'])
        ->assertSuccessful()
        ->json('audio_url');

    $this->get($url)->assertSuccessful()->assertStreamed()->assertStreamedContent('mp3-bytes');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/stream')
        && $request['text'] === 'Hola, soy Samantha.'
        && $request->header('xi-api-key')[0] === 'test-key');
});

it('rejects forged or expired speech tokens', function () {
    Http::fake();

    $this->get(route('os1.speech', ['t' => 'forged']))->assertNotFound();

    $expired = Crypt::encryptString(json_encode(['text' => 'Hi', 'expires' => now()->subMinute()->timestamp]));
    $this->get(route('os1.speech', ['t' => $expired]))->assertStatus(410);

    Http::assertNothingSent();
});

it('serves fixed lines as cacheable mp3s, synthesized once', function () {
    $urls = Os1Controller::voiceUrls();

    expect($urls)->toHaveKeys(['en', 'es'])
        ->and($urls['es'])->toHaveKeys(['greeting', 'name', 'review']);

    $this->get($urls['es']['greeting'])
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'audio/mpeg')
        ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public')
        ->assertCookieMissing('laravel_session');
    $this->get($urls['es']['greeting'])->assertSuccessful();

    Audio::assertGenerated(fn ($prompt) => $prompt->contains(Samantha::GREETINGS['es']));
    expect(Cache::has('os1.voice.'.Str::afterLast(Str::beforeLast($urls['es']['greeting'], '.mp3'), '-')))->toBeTrue();
});

it('404s fixed lines with a stale hash or unknown line', function () {
    $this->get('/os1/voice/greeting-en-000000000000.mp3')->assertNotFound();
    $this->get('/os1/voice/nope-en-000000000000.mp3')->assertNotFound();

    Audio::assertNothingGenerated();
});
