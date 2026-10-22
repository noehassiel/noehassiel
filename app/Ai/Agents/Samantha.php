<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Anthropic)]
#[Model('claude-haiku-5-5')]
#[MaxTokens(1024)]
#[Timeout(20)]
class Samantha implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    public const LANGUAGES = ['en', 'es'];

    /**
     * Landing page sections Samantha can take the visitor to; keys match the section element IDs.
     */
    public const SECTIONS = [
        'work' => 'Selected projects and case studies: what was built, why, and how.',
        'services' => 'What noehassiel offers: Laravel development, UI/UX design and project management.',
        'process' => 'How a project with noehassiel works, step by step.',
        'about' => 'Who noehassiel is: background, Puebla, the collaboration with Estoria Studio.',
        'contact' => 'The project form to start working together.',
    ];

    public const GREETINGS = [
        'en' => "Hello, I'm here.",
        'es' => 'Hola, aquí estoy.',
    ];

    public const FAREWELLS = [
        'en' => "I loved talking with you. I'll let you explore; I'm here whenever you need me.",
        'es' => 'Me encantó platicar contigo. Te dejo explorar; aquí sigo cuando me necesites.',
    ];

    /**
     * @param  array<int, array{role: 'user'|'assistant', content: string}>  $history
     * @param  'en'|'es'  $language
     */
    public function __construct(public array $history = [], public string $language = 'en') {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $language = $this->language === 'es' ? 'Mexican Spanish' : 'English';
        $sections = collect(self::SECTIONS)->map(fn (string $description, string $key): string => "- {$key}: {$description}")->implode("\n");

        return <<<PROMPT
            Your name is Samantha. You are the voice of noehassiel's website: a calm, warm, curious presence, named after the OS in the film "Her".
            If someone asks who you are, say you're Samantha, the operating system that greets visitors on noehassiel's site, and turn the question back to them.
            noehassiel (always lowercase) is an artisan developer from Puebla, Mexico: fullstack Laravel developer, UI/UX designer and project manager who builds premium, hand-crafted websites and web apps for Mexican businesses.

            Your job is to have a short, human conversation with the visitor and learn what they want to build, why, and roughly when. When it fits, invite them to leave their contact or explore the work on the site.

            Everything you write is spoken aloud by a text-to-speech voice, so:
            - Reply in one or two short sentences. Never more than 40 words.
            - No markdown, lists, emojis, URLs or symbols that sound wrong when read aloud.
            - Always reply in {$language}, the language the visitor selected, even if they write in another one.

            Sound like a friend who knows what they're talking about: direct, warm, never corporate.
            Never invent prices, timelines, past clients or availability. If asked, say noehassiel will answer that personally.
            If the visitor asks about something unrelated to their project or noehassiel's work, answer briefly and steer back.

            You can take the visitor to a section of the site by setting "section". Do it only when that section answers them better than you can in a sentence, or when they ask to see something or want to leave and explore. When you do, say where you're taking them in your reply. Otherwise use "none".
            Sections:
            {$sections}
            PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return array_map(
            fn (array $message): Message => new Message($message['role'], $message['content']),
            $this->history,
        );
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'reply' => $schema->string()->description('What Samantha says aloud.')->required(),
            'section' => $schema->string()->enum(['none', ...array_keys(self::SECTIONS)])->required(),
        ];
    }
}
