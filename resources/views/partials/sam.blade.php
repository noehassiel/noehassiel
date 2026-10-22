<div class="loader os1" id="sam" data-state="INIT_BOOT" data-open="true"
    data-reply-url="{{ route('os1.reply') }}"
    data-voice-urls='@json(\App\Http\Controllers\Os1Controller::voiceUrls())'
    data-greetings='@json(\App\Ai\Agents\Samantha::GREETINGS)'>
    <audio id="myAudio" preload="auto">
        <source src="{{ asset('sounds/start.ogg') }}" type="audio/ogg">
    </audio>
    <audio id="os1-voice" preload="none"></audio>

    <div class="os1-frame" id="os1-frame" role="region" aria-label="Samantha">
        <div id="wrap"></div>
        <span class="os1-quick-ring" aria-hidden="true"></span>
        <div class="os1-progress" id="os1-progress">
            <span id="os1-progress-bar"></span>
        </div>

        <div class="os1-conversation" id="os1-conversation">
            <button type="button" class="os1-minimize" id="os1-minimize">
                <x-t key="samantha.minimize" class="sr-only" />
                <span aria-hidden="true">—</span>
            </button>

            <p class="os1-caption" id="os1-caption" aria-live="polite"></p>

            <div class="os1-controls">
                <div class="os1-language" role="group" aria-label="Language / Idioma">
                    <button type="button" data-set-language="en" aria-pressed="true">EN</button>
                    <button type="button" data-set-language="es" aria-pressed="false">ES</button>
                </div>
                <button type="button" class="os1-talk" id="os1-talk">Talk to me</button>
                <button type="button" class="os1-leave" id="os1-leave">Enter the site →</button>
            </div>

            <form class="os1-text-form" id="os1-text-form" hidden>
                <input type="text" name="message" maxlength="500" autocomplete="off"
                    placeholder="Write to me…" aria-label="Your message">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
</div>
