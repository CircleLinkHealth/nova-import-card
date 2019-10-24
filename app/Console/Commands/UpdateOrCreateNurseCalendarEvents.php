<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\FullCalendar\FullCalendarService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Console\Command;

class UpdateOrCreateNurseCalendarEvents extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check yesterday dates in nurse_contact_window, compare with activities and update or create';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateOrCreateNurseCalendarEvents';
    /**
     * @var FullCalendarService
     */
    private $service;

    /**
     * Create a new command instance.
     *
     * @param FullCalendarService $service
     */
    public function __construct(FullCalendarService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function checkForActivities($userId, $yesterday)
    { //@todo:this can be extended to gain more control and options in the future. For now just checking if they worked
        return PageTimer::where('provider_id', $userId)
            ->where('start_time', '>=', Carbon::parse($yesterday)->startOfDay())
            ->where('start_time', '<=', Carbon::parse($yesterday)->endOfDay())
            ->exists();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $yesterday   = Carbon::yesterday()->toDateString();
        $today       = Carbon::parse(now())->endOfDay()->toDateTimeString();
        $startOfWeek = Carbon::parse($today)->startOfWeek();
        $endOfWeek   = Carbon::parse($today)->endOfWeek();
        //@todo: will also need to put this in an observer in case a nurse tries to delete something before the scheduled command has run.
        // @todo: So before deleted (in client side) it will be actually saved in DB for future use (case: admin wants to check past event).
        NurseContactWindow::with('nurse', 'nurse.workhourables', 'nurse.user')
//            ->where('repeat_frequency', '!=', 'does_not_repeat')
//            ->orWhereNull('repeat_frequency')
            ->whereHas('nurse', function ($q) {
                $q->where('status', 'active');
            })
            ->chunk(50, function ($nurseContactWindows) use ($yesterday, $today, $startOfWeek, $endOfWeek) {
                collect($nurseContactWindows)->map(function ($window) use ($yesterday, $today, $startOfWeek, $endOfWeek) {
                    $dayOfWeek = $window->day_of_week;
                    $weekMap = createWeekMap(Carbon::parse($today)->toDateString());
                    $recurringDateFallingInCurrentWeek = $weekMap[$dayOfWeek]; //@todo:this fix this
                    $savedDate = Carbon::parse($window->date)->toDateString();
                    $untilDate = null !== $window->until || $window->until < Carbon::parse($today)->toDateString() ? Carbon::parse($window->until)->toDateString() : Carbon::parse($today)->toDateString();
                    $recurringDates = CarbonPeriod::create(Carbon::parse($recurringDateFallingInCurrentWeek)->startOfWeek(), $untilDate)->toArray();
                    $userId = $window->nurse->user->id;
                    $yesterdayDateCollidesWithRecurringEvent = in_array(Carbon::parse($yesterday), $recurringDates);
                    $dayOfWeekForCurrentWeek = clhToCarbonDayOfWeek(Carbon::parse($recurringDateFallingInCurrentWeek)->dayOfWeek);
                    $userHasActivities = $this->checkForActivities($userId, $yesterday);
                    $validated = $this->validated($userHasActivities);

                    if ('does_not_repeat' !== $window->repeat_frequency && ! $yesterdayDateCollidesWithRecurringEvent) {
                        //if real saved date = yesterday date then update it immediately.
                        //**for me: it's either this or go to next check**
                        if ($savedDate === $yesterday) {
                            $this->updateEvent($window, $validated, $today);
                        }
                        //Else recreate the saved day_of_week to current's week date ($recurringDateFallingInCurrentWeek).
                        // No need for 'recurringDates ' array to has all dates range since im looking only for yesterday.
                        //**for me: Case of being in this week AND not = yesterday() its dismissed cause it should never happened
                        //and it if happens its irrelevant to check.**
                        if ( ! Carbon::parse($savedDate)->between($startOfWeek, $endOfWeek)) {
                            //Contains a slice which im recreating (fake - real data) of the recurring dates(they dont exists in DB).
                            //in case of an edited date that matches a recurring date 'in_array' will not allow that
                            //Doing this reduced arrays of 3.505 items to 4-5 items count
                            if ($recurringDateFallingInCurrentWeek === $yesterday) {
                                $this->updateOlderVersionWindow($window, $recurringDateFallingInCurrentWeek, $dayOfWeekForCurrentWeek, $validated, $today);
                            }
                        }
                    }

                    if ($yesterdayDateCollidesWithRecurringEvent) {
                        $this->updateOlderVersionWindow($window, $dayOfWeekForCurrentWeek, $recurringDateFallingInCurrentWeek, $validated, $today);
                    }

                    if ($savedDate === $yesterday && ! $yesterdayDateCollidesWithRecurringEvent) {
                        $this->updateEvent($window, $validated, $today);
                    }
                });
            });
        //@todo: Inform someone
    }

    /**
     * @param $window
     * @param $recurringDateFallingInCurrentWeek
     * @param $dayOfWeekForCurrentWeek
     * @param $validated
     */
    public function insertWindow($window, $recurringDateFallingInCurrentWeek, $dayOfWeekForCurrentWeek, $validated)
    {//it should be null - extra check
        $repeatFrequency = $window->repeat_frequency;
        if (null === $repeatFrequency) {
            $repeatFrequency = 'weekly';
        }
        $window->insert(
            [
                'nurse_info_id'     => $window->nurse->id,
                'date'              => $recurringDateFallingInCurrentWeek,
                'day_of_week'       => $dayOfWeekForCurrentWeek,
                'window_time_start' => $window->window_time_start,
                'window_time_end'   => $window->window_time_end,
                'validated'         => $validated,
                'manually_edited'   => true,
                'repeat_frequency'  => $repeatFrequency,
                'created_at'        => $window->created_at,
                'updated_at'        => Carbon::parse(now()),
            ]
        );
    }

    public function updateEvent($window, $validated, $today)
    {
        $window->update(
            [
                'validated'       => $validated,
                'manually_edited' => true,
            ]
        );
    }

    public function updateOlderVersionWindow($window, $recurringDateFallingInCurrentWeek, $dayOfWeekForCurrentWeek, $validated, $today)
    {
        //Make old vrsn window stop repeating using deleted_at field
        $window->update(
            [
                'validated'          => $validated,
                'manually_edited'    => true,
                'created_at'         => $window->created_at,
                'updated_at'         => Carbon::parse(now()),
                'hide_from_calendar' => $today,
            ]
        );
        //And set a new version in its place - maybe will delete old version with a command in future
        $this->insertWindow($window, $recurringDateFallingInCurrentWeek, $dayOfWeekForCurrentWeek, $validated);
    }

    public function validated($userHasActivities)
    {
        $validated = 'worked';

        if (false === $userHasActivities) {
            $validated = 'not_worked';
        }

        return $validated;
    }
}
