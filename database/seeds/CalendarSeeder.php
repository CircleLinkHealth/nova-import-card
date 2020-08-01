<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\FullCalendar\NurseCalendarService;
use App\Jobs\CreateCalendarRecurringEventsJob;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * @var NurseCalendarService
     */
    private $calendarService;

    /**
     * CalendarSeeder constructor.
     */
    public function __construct(NurseCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Run the database seeds.
     *
     * @throws Exception
     *
     * @return void
     */
    public function run()
    {
        $practice = Practice::first();

        if ($practice) {
            //create ACTIVE nurse
            $users = factory(User::class, 8)->create(['saas_account_id' => $practice->saas_account_id])->each(function ($nurse) use ($practice) {
                $nurse->username = 'nurse';
                $nurse->auto_attach_programs = true;
                $nurse->email = 'nurse@example.org';
                $nurse->attachPractice($practice->id, [Role::whereName('care-center')->value('id')]);
                $nurse->program_id = $practice->id;
                $nurse->password = Hash::make('hello');
                $nurse->save();
                $nurse->nurseInfo()->create();
//                $this->command->info("nurse user $nurse->display_name seeded");
            });

//            $this->command->info("Users Created");
            foreach ($users as $user) {
                $date      = \Carbon\Carbon::parse(now())->copy()->subDays(random_int(1, 6))->toDateString();
                $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
                $user->nurseInfo()->update(['status' => 'active']);

                $window = $user->nurseInfo->windows()->create([
                    'nurse_info_id'     => $user->nurseInfo->id,
                    'date'              => $date,
                    'day_of_week'       => $dayOfWeek,
                    'window_time_start' => '10:00',
                    'window_time_end'   => '18:00',
                    'repeat_start'      => $date,
                    'repeat_frequency'  => null,
                    'until'             => null,
                    'validated'         => 'not_checked',
                ]);

                $nurseInfoId     = $user->nurseInfo->id;
                $workWeekStart   = Carbon::parse($date)->startOfWeek();
                $workHoursCreate = WorkHours::create(
                    [
                        'workhourable_type'                           => Nurse::class,
                        'workhourable_id'                             => $nurseInfoId,
                        'work_week_start'                             => Carbon::parse($workWeekStart)->toDateString(),
                        strtolower(clhDayOfWeekToDayName($dayOfWeek)) => 5,
                    ]
                );

                $eventDateToDayName = clhDayOfWeekToDayName($window->day_of_week);
                $workHours          = $window->nurse->workhourables->where('workhourable_id', $nurseInfoId)->pluck(lcfirst($eventDateToDayName))->first();

                $windowData = [
                    'repeat_freq'       => $window->repeat_frequency,
                    'date'              => $date,
                    'until'             => $window->until,
                    'window_time_start' => $window->window_time_start,
                    'window_time_end'   => $window->window_time_end,
                    'work_hours'        => $workHours,
                ];

                $recurringEventsToSave = $this->calendarService->createRecurringEvents($nurseInfoId, $windowData);
                CreateCalendarRecurringEventsJob::dispatch($recurringEventsToSave, $window, null, $windowData['work_hours'])->onQueue('low');
//                $this->command->info("nurse id $nurseInfoId seeded");
            }
//            $this->command->info("Calendar Seeder DONE!!");
        }
    }
}
