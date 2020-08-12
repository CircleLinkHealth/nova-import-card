<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InvalidateCacheKey extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invalidate a specific key from the cache.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:invalidate-key {key}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $key = $this->argument('key');

        if (empty($key)) {
            $this->info('Empty key provided.');
        }

        $this->info("Invalidating key: $key");

        \Cache::forget($key);

        $this->info('Key forgotten.');
    }
}
