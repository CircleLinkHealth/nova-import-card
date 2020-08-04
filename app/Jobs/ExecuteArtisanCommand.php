<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Use this job to execute an artisan command
 * (if you want to execute job asynchronously).
 *
 * Class ExecuteArtisanCommand
 */
class ExecuteArtisanCommand implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $args;

    private string $commandName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $commandName, array $args)
    {
        $this->commandName = $commandName;
        $this->args        = $args;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->args)) {
            \Artisan::call($this->commandName);

            return;
        }
        \Artisan::call($this->commandName, $this->args);
    }
}
