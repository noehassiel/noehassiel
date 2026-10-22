<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration is disabled', function () {
    $this->get('/register')->assertNotFound();
});
