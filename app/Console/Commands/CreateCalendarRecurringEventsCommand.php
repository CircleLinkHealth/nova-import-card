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
     */
    public function __construct(NurseCalendarService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     * //@todo:this breaks...memory exausted.
     *
     * @return mixed
     */
    public function handle()
    {// I Can check current past events if they got worked or not by checking PageTimer,
        // however there is no way to know the hrs committed and the time range to work committed.
        //Should i check now for past events and mark them in different color
        // OR we ll start this functionality (checking if past committed events have activity) from development date and later?

        $today          = Carbon::parse(now())->toDateString();
        $currentWeekMap = createWeekMap($today);

        NurseContactWindow::with('nurse.user', 'nurse.workhourables')
            ->whereHas('nurse', function ($q) {
                $q->where('status', 'active');
                //@todo: case of nurse having windows - but are inactive during launch - and then becoming active again
            })->chunk(200, function ($nurseContactWindows) use ($currentWeekMap) {
                collect($nurseContactWindows)
                    ->transform(function ($window) use ($currentWeekMap) {
//                        If we re showing events from release date and after then use $newEventOriginalDate ELSE use $window->date
//                        $newEventOriginalDate = projection of oriiginal's event scheduled date to current's week date.
//                        I suggest using the 'newEventOriginalDate'. we have events with scheduled_date 2017-10-11 and we dont need them repeating till today.
//                         Also past events submitted work_hours cant be qualified as worked.
                        $projectionEventDate = $currentWeekMap[$window->day_of_week];
                        $nurseInfoId = $window->nurse->id;
                        $eventDateToDayName = clhDayOfWeekToDayName($window->day_of_week);
                        $workHours = $window->nurse->workhourables->where('workhourable_id', $nurseInfoId)->pluck(lcfirst($eventDateToDayName))->first();

                        $windowData = [
                            'repeat_freq'       => $window->repeat_frequency,
                            'date'              => $projectionEventDate,
                            'until'             => $window->until,
                            'window_time_start' => $window->window_time_start,
                            'window_time_end'   => $window->window_time_end,
                            'work_hours'        => $workHours,
                        ];

                        $recurringEventsToSave = $this->service->createRecurringEvents($nurseInfoId, $windowData);
                        CreateCalendarRecurringEventsJob::dispatch($recurringEventsToSave, $window, null, $windowData['work_hours'])->onQueue('low');
                    });
            });

        return info('Success');
        //@todo: Inform someone
    }
}
