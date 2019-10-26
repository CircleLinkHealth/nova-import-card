<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\FullCalendar\NurseCalendarService;
use App\Jobs\CreateCalendarRecurringEvents;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Console\Command;

class CreateNurseCalendarRecurringEvents extends Command
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
    protected $signature = 'command:createNurseCalendarRecurringEvents';
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
    {
        NurseContactWindow::with('nurse.user')->chunk(200, function ($nurseContactWindows) {
            collect($nurseContactWindows)->map(function ($window) {
                $givenDateWeekMap = createWeekMap($window->date); //see comment in helpers.php
                $eventDate = $givenDateWeekMap[$window->day_of_week]; //converts the date that event was saved to - date that event is scheduled for
                $repeatEventByDefaultUntil = Carbon::parse($eventDate)->copy()->addMonths(6)->toDateString();
                $recurringDates = collect(CarbonPeriod::create($eventDate, $repeatEventByDefaultUntil));

                //check for activities and get collection of windows to update or create
                $recurringEventsToSave = $recurringDates->map(function ($date) use ($window, $eventDate, $repeatEventByDefaultUntil) {
                    $defaultRepeatFreq = 'weekly';
                    $userId = $window->nurse->user_id;
                    $today = Carbon::parse(now())->toDateString();

                    $activityOnEventDate = PageTimer::where('provider_id', $userId)
                        ->where('start_time', '>=', Carbon::parse($date)->startOfDay())
                        ->where('start_time', '<=', Carbon::parse($today)->endOfDay())
                        ->exists();

                    $validated = false === $activityOnEventDate ? 'not_worked' : 'worked';

                    if (Carbon::parse($window->date)->toDateTimeString() > $today) {
                        $validated = 'not_checked';
                    }

                    return [
                        'nurse_info_id'     => $window->nurse->id,
                        'date'              => $date,
                        'day_of_week'       => Carbon::parse($eventDate)->dayOfWeek,
                        'window_time_start' => $window->window_time_start,
                        'window_time_end'   => $window->window_time_end,
                        'validated'         => $validated,
                        'manually_created'  => true,
                        'repeat_frequency'  => $defaultRepeatFreq,
                        'until'             => $repeatEventByDefaultUntil,
                        'created_at'        => Carbon::parse(now())->toDateTimeString(),
                        'updated_at'        => Carbon::parse(now())->toDateTimeString(),
                    ];
                });

                CreateCalendarRecurringEvents::dispatch($recurringEventsToSave, $window)->onQueue('low');
            });
        });

        //@todo: Inform someone
    }
}
