<?php

namespace App\Console\Commands;

use App\NurseContactWindow;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\GoogleCalendar\Event;

class ExportNurseSchedulesToGoogleCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseSchedule:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports Nurses Schedules to a Google Calendar';
    protected $nurseContactWindows;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NurseContactWindow $nurseContactWindow)
    {
        parent::__construct();

        $this->nurseContactWindows = $nurseContactWindow;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $events = Event::get()->map(function ($event) {
            $event->delete();
        });

        $windows = $this->nurseContactWindows->getScheduleForAllNurses();

        foreach ($windows as $window) {
            $nurseTz = $window->nurse->user->timezone;

            $startDate = "{$window->date->format('Y-m-d')} {$window->window_time_start}";
            $endDate = "{$window->date->format('Y-m-d')} {$window->window_time_end}";

            $startDateTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $startDate,
                $nurseTz
            )->setTimezone('America/New_York');

            $endDateTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $endDate,
                $nurseTz
            )->setTimezone('America/New_York');

            $calendarEvent = Event::create([
                'name'          => $window->nurse->user->fullName,
                'startDateTime' => $startDateTime,
                'endDateTime'   => $endDateTime,
            ]);
        }
    }
}
