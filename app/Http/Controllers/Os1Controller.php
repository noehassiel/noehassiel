<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Samantha;
use App\Http\Requests\Os1ReplyRequest;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Audio;
use Laravel\Ai\Enums\Lab;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Os1Controller extends Controller
{
    private const HISTORY_KEY = 'os1.history';

    private const VOICE_MODEL = 'eleven_flash_v2_5';

    public const BRIEF_LINES = ['name', 'about', 'type', 'budget', 'timeline', 'contact', 'review'];

    /**
     * URLs for every fixed line Samantha says, per language. The hash in each URL changes with the text
     * or the voice, so the CDN can cache them forever without ever serving a stale line.
     *
     * @return array<string, array<string, string>>
     */
    public static function voiceUrls(): array
    {
        return collect(Samantha::LANGUAGES)->mapWithKeys(fn (string $language): array => [
            $language => collect(['greeting', ...self::BRIEF_LINES])->mapWithKeys(fn (string $line): array => [
                $line => route('os1.voice', [
                    'line' => $line,
                    'language' => $language,
                    'hash' => self::lineHash(self::lineText($line, $language)),
                ]),
            ])->all(),
        ])->all();
    }

    /**
     * Fixed lines live in the shared cache (the filesystem isn't shared between instances on Laravel Cloud)
     * and are served as cacheable .mp3 files, so after the first request the CDN answers instantly.
     */
    public function voice(string $line, string $language, string $hash): Response
    {
        abort_unless(in_array($line, ['greeting', ...self::BRIEF_LINES], true), 404);
        abort_unless(in_array($language, Samantha::LANGUAGES, true), 404);

        $text = self::lineText($line, $language);
        abort_unless(hash_equals(self::lineHash($text), $hash), 404);

        $audio = Cache::rememberForever("os1.voice.{$hash}", fn (): string => Audio::of($text)
            ->voice(config('services.os1.voice_id'))
            ->generate(provider: Lab::ElevenLabs, model: self::VOICE_MODEL)
            ->audio);

        return response(base64_decode($audio), 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
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
     * Streams a reply's audio straight from ElevenLabs, so playback starts before synthesis finishes.
     * The token is the encrypted text, so only replies this app produced can be spoken.
     */
    public function speech(Request $request): StreamedResponse
    {
        try {
            $payload = json_decode(Crypt::decryptString((string) $request->query('t')), true);
        } catch (DecryptException) {
            abort(404);
        }

        abort_if(($payload['expires'] ?? 0) < now()->timestamp, 410);

        $body = Http::withHeaders(['xi-api-key' => config('ai.providers.eleven.key')])
            ->withOptions(['stream' => true])
            ->accept('audio/mpeg')
            ->timeout(30)
            ->post('https://api.elevenlabs.io/v1/text-to-speech/'.config('services.os1.voice_id').'/stream?output_format=mp3_44100_64', [
                'text' => $payload['text'],
                'model_id' => self::VOICE_MODEL,
            ])
            ->throw()
            ->toPsrResponse()
            ->getBody();

        return response()->stream(function () use ($body): void {
            while (! $body->eof()) {
                echo $body->read(4096);

                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();
            }
        }, 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'no-store',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * @return 'en'|'es'
     */
    private function language(mixed $language): string
    {
        return in_array($language, Samantha::LANGUAGES, true) ? $language : 'en';
    }

    /**
     * Return the text right away with a short-lived URL that streams its audio.
     */
    private function speak(string $text, bool $finished = false, ?string $section = null): JsonResponse
    {
        $token = Crypt::encryptString(json_encode([
            'text' => $text,
            'expires' => now()->addMinutes(5)->timestamp,
        ]));

        return response()->json([
            'text' => $text,
            'audio_url' => route('os1.speech', ['t' => $token]),
            'finished' => $finished,
            'section' => $section,
        ]);
    }

    private static function lineText(string $line, string $language): string
    {
        return $line === 'greeting'
            ? Samantha::GREETINGS[$language]
            : __("landing.brief.questions.{$line}", locale: $language);
    }

    private static function lineHash(string $text): string
    {
        return substr(md5($text.config('services.os1.voice_id').self::VOICE_MODEL), 0, 12);
    }
}
