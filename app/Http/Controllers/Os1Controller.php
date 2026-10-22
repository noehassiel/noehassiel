<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Samantha;
use App\Http\Requests\Os1ReplyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Audio;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\AudioResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Os1Controller extends Controller
{
    private const HISTORY_KEY = 'os1.history';

    private const VOICE_MODEL = 'eleven_flash_v2_5';

    public const BRIEF_LINES = ['name', 'about', 'type', 'budget', 'timeline', 'contact', 'review'];

    /**
     * The greeting is synthesized once and served from disk, so it plays instantly and costs nothing after the first visit.
     */
    public function greeting(Request $request): BinaryFileResponse
    {
        return $this->cachedSpeech(Samantha::GREETINGS[$this->language($request->query('language'))]);
    }

    /**
     * Samantha's fixed lines for the project brief, e.g. "What's your name?".
     */
    public function line(Request $request, string $key): BinaryFileResponse
    {
        abort_unless(in_array($key, self::BRIEF_LINES, true), 404);

        return $this->cachedSpeech(__("landing.brief.questions.{$key}", locale: $this->language($request->query('language'))));
    }

    /**
     * Fixed lines are synthesized once and served from disk, so they play instantly and cost nothing after the first visit.
     */
    private function cachedSpeech(string $text): BinaryFileResponse
    {
        $path = 'os1/line-'.md5($text.config('services.os1.voice_id')).'.mp3';

        if (! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, $this->synthesize($text)->content());
        }

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function reply(Os1ReplyRequest $request): JsonResponse
    {
        $language = $this->language($request->validated('language'));

        $conversationId = $request->validated('conversation');
        $stored = $request->session()->get(self::HISTORY_KEY);

        // Each page visit is a new conversation; only the current one is kept in the session
        /** @var array<int, array{role: 'user'|'assistant', content: string}> $history */
        $history = ($stored['id'] ?? null) === $conversationId ? $stored['messages'] : [
            ['role' => 'user', 'content' => '(The visitor just arrived.)'],
            ['role' => 'assistant', 'content' => Samantha::GREETINGS[$language]],
        ];

        $turns = collect($history)->where('role', 'user')->count() - 1;

        if ($turns >= config('services.os1.max_turns')) {
            return $this->speak(Samantha::FAREWELLS[$language], finished: true);
        }

        $message = $request->validated('message');
        $response = (new Samantha($history, $language))->prompt($message);
        $text = trim($response['reply']);
        $section = array_key_exists($response['section'], Samantha::SECTIONS) ? $response['section'] : null;

        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $text];
        $request->session()->put(self::HISTORY_KEY, ['id' => $conversationId, 'messages' => $history]);

        return $this->speak($text, section: $section);
    }

    /**
     * @return 'en'|'es'
     */
    private function language(mixed $language): string
    {
        return in_array($language, Samantha::LANGUAGES, true) ? $language : 'en';
    }

    private function speak(string $text, bool $finished = false, ?string $section = null): JsonResponse
    {
        $audio = $this->synthesize($text);

        return response()->json([
            'text' => $text,
            'audio' => $audio->audio,
            'mime' => $audio->mimeType() ?? 'audio/mpeg',
            'finished' => $finished,
            'section' => $section,
        ]);
    }

    private function synthesize(string $text): AudioResponse
    {
        return Audio::of($text)
            ->voice(config('services.os1.voice_id'))
            ->generate(provider: Lab::ElevenLabs, model: self::VOICE_MODEL);
    }
}
