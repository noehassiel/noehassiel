<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('renders the os1 launch sequence on the landing', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('id="sam"', false)
        ->assertSee('data-state="INIT_BOOT"', false)
        ->assertSee('id="os1-frame"', false)
        ->assertSee('id="os1-progress-bar"', false)
        ->assertSee('class="os1-window"', false)
        ->assertSee('class="os1-dock-icon"', false)
        ->assertDontSee('three.js/r77', false);
});
