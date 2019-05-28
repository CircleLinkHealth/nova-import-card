<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;
use Spatie\GoogleCalendar\Event as GoogleCalendarEvent;

class ImportNurseScheduleFromGoogleCalendar extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Nurse Schedule from Google Calendar.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseSchedule:import';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $nextWeek = Carbon::now()->addWeek(1);

        $startDateTime = $nextWeek->startOfWeek()->copy();
        $endDateTime   = $nextWeek->endOfWeek()->copy();

        $events = GoogleCalendarEvent::get($startDateTime, $endDateTime);

        if (empty($events)) {
            return;
        }

        foreach ($events as $event) {
            try {
                $nurseId = Nurse::$nurseMap[$event->name];
            } catch (\Exception $e) {
                continue;
            }

            $nurse  = User::find($nurseId);
            $window = timestampsToWindow($event->startDateTime, $event->endDateTime);

            NurseContactWindow::updateOrCreate([
                'nurse_info_id'     => $nurse->nurseInfo->id,
                'day_of_week'       => $window['day'],
                'window_time_start' => $window['start'],
                'window_time_end'   => $window['end'],
            ]);
        }
    }
}
