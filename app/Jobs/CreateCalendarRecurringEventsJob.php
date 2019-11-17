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

class CreateCalendarRecurringEventsJob implements ShouldQueue
{
    /*
     *  Note: This command does NOT delete original data from table. They are left with repeat_frequency == null.
     * We can delete them later.
     *
     */
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private $recurringEventsToSave;
    private $updateOriginalWindow;
    private $window;

    /**
     * Create a new job instance.
     *
     * @param $recurringEventsToSave
     * @param $window
     * @param $updateOriginalWindow
     */
    public function __construct($recurringEventsToSave, $window, $updateOriginalWindow = null)
    {
        $this->recurringEventsToSave = $recurringEventsToSave;
        $this->window                = $window;
        $this->updateOriginalWindow  = $updateOriginalWindow;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {// what if nurseWindows table is empty(should never be)but then there will not exist any $this->>window
        foreach ($this->recurringEventsToSave as $event) {
            $updateOrInsert = empty($this->updateOriginalWindow) ? false : $this->updateOriginalWindow;
            if ($updateOrInsert) {
                $this->updateOrCreateWindow($event);
            } else {
                $this->window::create($event);
            }
        }
    }

    /**
     * @param $event
     */
    public function updateOrCreateWindow($event)
    {
        $this->window::updateOrCreate(
            [
                'nurse_info_id' => $event['nurse_info_id'],
                'date'          => $event['date'],
            ],
            [
                'date'              => $event['date'],
                'day_of_week'       => $event['day_of_week'],
                'window_time_start' => $event['window_time_start'],
                'window_time_end'   => $event['window_time_end'],
                'repeat_start'      => $event['repeat_start'],
                'until'             => $event['until'],
                'repeat_frequency'  => $event['repeat_frequency'],
                'validated'         => $event['validated'],
                'created_at'        => $event['created_at'],
                'updated_at'        => $event['updated_at'],
            ]
        );
    }
}
