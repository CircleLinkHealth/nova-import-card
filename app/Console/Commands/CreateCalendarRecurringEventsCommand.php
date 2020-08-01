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
     * //@return mixed.
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');

        $today          = Carbon::parse(now())->toDateString();
        $currentWeekMap = createWeekMap($today);

        NurseContactWindow::with('nurse.user', 'nurse.workhourables')
            ->whereHas('nurse', function ($q) {
                $q->where('status', 'active');
            })->chunk(200, function ($nurseContactWindows) use ($currentWeekMap) {
                collect($nurseContactWindows)
                    ->transform(function ($window) use ($currentWeekMap) {
//                        $projectionEventDate = projection of original's event scheduled date to current's week date.
//                        I suggest using the $projectionEventDate cause we have events with scheduled_date 2017-10-11 and we dont need them repeating till today.
//                        Also with the functionality before this feature we cant know if the hrs committed in the past (< startOfThisWeek) got worked or not.
                        $projectionEventDate = $currentWeekMap[$window->day_of_week];
                        $nurseInfoId = $window->nurse->id;
                        $eventDateToDayName = clhDayOfWeekToDayName($window->day_of_week);

                        $workHours = $window->nurse->workhourables->where('workhourable_id', $nurseInfoId)->pluck(lcfirst($eventDateToDayName))->first();

                        $windowData = [
                            'repeat_freq'       => $window->repeat_frequency,
                            'date'              => $projectionEventDate,
                            'until'             => Carbon::parse($projectionEventDate)->copy()->addWeek(2)->toDateString(),
                            'window_time_start' => $window->window_time_start,
                            'window_time_end'   => $window->window_time_end,
                            'work_hours'        => $workHours,
                        ];

                        $recurringEventsToSave = $this->service->createRecurringEvents($nurseInfoId, $windowData);
                        CreateCalendarRecurringEventsJob::dispatch($recurringEventsToSave, $window, null, $windowData['work_hours'])->onQueue('low');
                    });
            });

        $startDate = Carbon::parse($today)->startOfDay()->toDateString();
        $endDate   = Carbon::parse($startDate)->endOfDay()->toDateString();

        return info('Success! "Calendar Recurring Events" have been created');
    }
}
