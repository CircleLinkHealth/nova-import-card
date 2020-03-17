<?php

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PopulateNursePerformanceTable extends Seeder
{
    use UserHelpers;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practice = Practice::first();
        $faker = Factory::create();
        $patientInfoCCMStatutes = $faker->randomElement([Patient::UNREACHABLE, Patient::ENROLLED]);
        $randomHours = $faker->randomElement([3, 4]);
        $weekStarts = \Carbon\Carbon::parse(now())->startOfWeek();

        //        Creates weekly dates
        $weeklyDates = [];
        for ($x = 0; $x <= 6; $x++) {
            $weeklyDates[] = \Carbon\Carbon::parse($weekStarts)->copy()->addDay($x);
        }

        //        Create Nurse user
        $user = $this->createUser($practice->id, 'care-center');
        //        Create patient
        $patient = $this->createUser($practice->id, 'participant', $patientInfoCCMStatutes);

        //        Create Windows and hours
        $nurseWindows = [];
        $workHours = [];
        /** @var \Carbon\Carbon $date */
        foreach ($weeklyDates as $date) {

            /** @var Carbon $start */
            $start = $date->copy()->midDay()->subHours($randomHours);
            /** @var Carbon $end */
            $end = $date->copy()->midDay()->addHours($randomHours);

            $window = $this->createWindowForNurse($user->nurseInfo, $start, $end);
            $nurseWindows[] = $window;

                $workHour = WorkHours::updateOrCreate(
                    [
                        'workhourable_type' => Nurse::class,
                        'workhourable_id' => $user->nurseInfo->id,
                    ],
                    [
                        'work_week_start' => $date->copy()->startOfWeek(),
                        strtolower(clhDayOfWeekToDayName(carbonToClhDayOfWeek($date->copy()->dayOfWeek))) => $start->diffInHours($end),
                    ]
                );
            $workHours[] = $workHour;
        }

        $x = 1;



    }
}
