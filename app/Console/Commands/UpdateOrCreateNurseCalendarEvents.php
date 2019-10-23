<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\FullCalendar\FullCalendarService;
use Carbon\Carbon;
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
            ->whereHas('nurse', function ($q) {
                $q->where('status', 'active');
            })
            ->chunk(50, function ($nurseContactWindows) use ($yesterday, $today, $startOfWeek, $endOfWeek) {
                collect($nurseContactWindows)->map(function ($window) use ($yesterday, $today, $startOfWeek, $endOfWeek) {
                    $dayOfWeek = $window->day_of_week;
                    $savedDate = Carbon::parse($window->date)->toDateString();
                    $userId = $window->nurse->user->id;
                    if ('does_not_repeat' !== $window->repeat_frequency) {
                        //if real saved date = yesterday date then update it immediately.
                        //**for me: it's either this or go to next check**
                        if ($savedDate === $yesterday) {
                            $this->update($window, $yesterday, $userId);
                        }
                        //Else recreate the saved day_of_week to current's week date ($recurringDateFallingInCurrentWeek).
                        // No need to recreate an array with all dates range since im looking only for yesterday.
                        //**for me: Case of being in this week AND not = yesterday() its dismissed cause it should never happened
                        //and it if happens its irrelevant to check.**
                        if ( ! Carbon::parse($savedDate)->between($startOfWeek, $endOfWeek)) {
                            $weekMap = createWeekMap(Carbon::parse($today)->toDateString());
                            $recurringDateFallingInCurrentWeek = $weekMap[$dayOfWeek];
                            if ($recurringDateFallingInCurrentWeek === $yesterday) {
                                $userHasActivities = $this->checkForActivities($userId, $yesterday);
                                $validated = $this->validated($userHasActivities);

                                $window->insert(
                                    [
                                        'nurse_info_id'     => 174,
                                        'date'              => $savedDate,
                                        'day_of_week'       => $dayOfWeek,
                                        'window_time_start' => $window->window_time_start,
                                        'window_time_end'   => $window->window_time_end,
                                        'validated'         => $validated,
                                        'manually_saved'    => true,
                                        'repeat_frequency'  => 'does_not_repeat', //maybe another enum: 'ignore'?
                                        'created_at'        => Carbon::parse(now()),
                                        'updated_at'        => Carbon::parse(now()),
                                    ]
                                );
                            }
                        }
                    } else {
                        if ($savedDate === $yesterday) {
                            $this->update($window, $yesterday, $userId);
                        }
                    }
                });
            });
        //@todo: Inform someone
    }

    public function update($window, $yesterday, $userId)
    {
        $userHasActivities = $this->checkForActivities($userId, $yesterday);
        $validated         = $this->validated($userHasActivities);
        $window->update(
            [
                'validated'      => $validated,
                'manually_saved' => true,
            ]
        );
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
