<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\FullCalendar\NurseCalendarService;
use App\Jobs\CreateCalendarRecurringEventsJob;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use Illuminate\Console\Command;

class CreateCalendarRecurringEventsCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Go through contact_windows and create recurring dates';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:createCalendarRecurringEventsForPastWindows';
    /**
     * @var NurseCalendarService
     */
    private $service;

    /**
     * Create a new command instance.
     *
     * @param NurseCalendarService $service
     */
    public function __construct(NurseCalendarService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {// I Can check current past events if they got worked or not by checking PageTimer,
        // however there is no way to know the hrs committed and the time range to work committed.
        //Should i check now for past events and mark them in different color
        // OR we ll start this functionality (checking if past committed events have activity) from development date and later?

        $today              = Carbon::parse(now())->toDateString();
        $currentDateWeekMap = createWeekMap($today); //see comment in helpers.php
        NurseContactWindow::with('nurse.user')
            ->whereHas('nurse', function ($q) {
                $q->where('status', 'active'); //@todo: case of nurse becoming active from inactivity
            })
            ->chunk(200, function ($nurseContactWindows) use ($currentDateWeekMap) {
                collect($nurseContactWindows)
                    ->map(function ($window) use ($currentDateWeekMap) {
                        $eventDate = $currentDateWeekMap[$window->day_of_week];
                        $nurseInfoId = $window->nurse->id;
                        $windowTimeStart = $window->window_time_start;
                        $windowTimeEnd = $window->window_time_end;
                        $recurringEventsToSave = $this->service->createRecurringEvents($eventDate, $nurseInfoId, $windowTimeStart, $windowTimeEnd);
                        CreateCalendarRecurringEventsJob::dispatch($recurringEventsToSave, $window)->onQueue('low');
                    });
            });

        return info('Success');
        //@todo: Inform someone
    }
}
