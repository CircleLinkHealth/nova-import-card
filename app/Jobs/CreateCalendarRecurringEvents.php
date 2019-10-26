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

class CreateCalendarRecurringEvents implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private $recurringEventsToSave;
    private $window;

    /**
     * Create a new job instance.
     *
     * @param $recurringEventsToSave
     * @param $window
     */
    public function __construct($recurringEventsToSave, $window)
    {
        $this->recurringEventsToSave = $recurringEventsToSave;
        $this->window                = $window;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        foreach ($this->recurringEventsToSave as $event) {
            $this->window->insert($event);
        }
    }
}
