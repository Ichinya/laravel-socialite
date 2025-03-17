<?php

namespace Ichinya\LaravelSocialite\Commands;

use Illuminate\Console\Command;

class LaravelSocialiteCommand extends Command
{
    public $signature = 'laravel-socialite';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
