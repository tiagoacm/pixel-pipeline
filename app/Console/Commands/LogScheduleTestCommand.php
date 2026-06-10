<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:log-schedule-test')]
#[Description('Write a scheduled test message to the log')]
class LogScheduleTestCommand extends Command
{
    public function handle(): int
    {
        Log::info('Teste comando schedule');

        return self::SUCCESS;
    }
}
