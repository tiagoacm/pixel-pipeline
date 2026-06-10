<?php

use Illuminate\Support\Facades\Log;

test('logs the scheduled message', function () {
    Log::spy();

    $this->artisan('app:log-schedule-test')
        ->assertSuccessful();

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Teste comando schedule');
});
