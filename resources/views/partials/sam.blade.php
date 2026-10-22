<div class="loader os1" id="sam" data-state="INIT_BOOT">
    <audio id="myAudio" preload="auto">
        <source src="{{ asset('sounds/start.ogg') }}" type="audio/ogg">
    </audio>

    <div class="os1-desktop" aria-hidden="true">
        <div class="os1-windows">
            @foreach (range(1, 5) as $window)
                <div class="os1-window">
                    <span class="os1-window-bar"></span>
                    <span class="os1-window-line"></span>
                    <span class="os1-window-line os1-window-line--short"></span>
                    <span class="os1-window-line"></span>
                </div>
            @endforeach
        </div>

        <div class="os1-dock">
            <div class="os1-dock-column">
                @foreach (range(1, 4) as $icon)
                    <span class="os1-dock-icon"></span>
                @endforeach
            </div>
            <div class="os1-dock-row">
                @foreach (range(1, 5) as $icon)
                    <span class="os1-dock-icon"></span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="os1-frame" id="os1-frame" role="img" aria-label="OS1 initializing">
        <div id="wrap"></div>
        <div class="os1-progress" id="os1-progress">
            <span id="os1-progress-bar"></span>
        </div>
    </div>
</div>
