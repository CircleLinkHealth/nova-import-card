<?php

use App\Call;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
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
        $randomPatientsToCreateForNursePerDay = $faker->randomElement([1, 2, 3]);
        $weekStarts = \Carbon\Carbon::parse(now())->startOfWeek();
        $weekEnds = Carbon::parse($weekStarts)->copy()->endOfWeek();
        $weekRange = $weekStarts->diffInDays($weekEnds);

        //        Creates weekly dates
        $weeklyDates = [];
        for ($x = 0; $x <= $weekRange; $x++) {
            $weeklyDates[] = \Carbon\Carbon::parse($weekStarts)->copy()->addDay($x);
        }

        $users = User::ofType('care-center')
            ->with(
                [
                    'nurseInfo' => function ($info) {
                        $info->with(
                            [
                                'windows',
                                'holidays',
                                'workhourables',
                            ]
                        );
                    },
                ]
            )
            ->whereHas(
                'nurseInfo',
                function ($info) {
                    $info->where('status', 'active')
                        ->when(isProductionEnv(), function ($info) {
                            $info->where('is_demo', false);
                        });
                }
            )->get();

        $collectCallStatuses = [];
        foreach ($users as $user) {
            $pageTimersForWeekForNurse = [];
            $calls = [];

            //        Create a patient
            $patient = $this->createUser($practice->id, 'participant', $patientInfoCCMStatutes);


            /** @var \Carbon\Carbon $date */
            foreach ($weeklyDates as $date) {

                $randomCallStatuses = $faker->randomElement([Call::REACHED, Call::NOT_REACHED, 'scheduled']);

                /** @var Carbon $start */
                $start = $date->copy()->midDay()->subHours($randomHours);
                /** @var Carbon $end */
                $end = $date->copy()->midDay()->addHours($randomHours);

                //        Create Windows
                $window = $this->createWindowForNurse($user->nurseInfo, $start, $end);

                //       Create hours
                WorkHours::updateOrCreate(
                    [
                        'workhourable_type' => Nurse::class,
                        'workhourable_id' => $user->nurseInfo->id,
                    ],
                    [
                        'work_week_start' => $date->copy()->startOfWeek(),
                        strtolower(clhDayOfWeekToDayName(carbonToClhDayOfWeek($date->copy()->dayOfWeek))) => $start->diffInHours($end),
                    ]
                );

//                Create PageTimer
                $pageTimerAdjustStartEndTime = $faker->randomElement([1, 2, 3]);
                $pageTimerStart = $start->addHours($pageTimerAdjustStartEndTime);
                $pageTimerEnd = $end->subHours($pageTimerAdjustStartEndTime);
                $billableDuration = $pageTimerStart->diffInSeconds($pageTimerEnd);

                if ($billableDuration === 0) {
                    $billableDuration = 120;
                }

                $pageTimer = PageTimer::create(
                    [
                        'patient_id' => $patient->id,
                        'billable_duration' => $billableDuration,
                        'duration' => $billableDuration,
                        'duration_unit' => 'seconds',
                        'provider_id' => $user->id,
                        'start_time' => $pageTimerStart,
                        'end_time' => $pageTimerEnd,
                    ]
                );

                $pageTimersForWeekForNurse[] = $pageTimer;
//            Create Calls
                $callStatus = $randomCallStatuses;
                $collectCallStatuses[] = $callStatus;
                $lastStatusEntered = end($collectCallStatuses);
                $beforeLastStatusEntered = $collectCallStatuses[count($collectCallStatuses) - 1];

                //            Ensure that will enter more "scheduled" status (every two if not "scheduled" then force it)
                if (count($collectCallStatuses) !== 1 && ($lastStatusEntered !== 'scheduled' || $beforeLastStatusEntered !== 'scheduled')) {
                    $callStatus = 'scheduled';
                }

                $call = $user->calls()->create(
                    [
                        'service' => 'phone',
                        'status' => 'reached',
                        'inbound_cpm_id' => $patient->id,
                        'window_start' => \Carbon\Carbon::parse($window->window_time_start)->toTimeString(),
                        'window_end' => \Carbon\Carbon::parse($window->window_time_end)->toTimeString(),
                        'scheduled_date' => $date->copy()->toDateString(),
                        'called_date' => $date->copy()->startOfDay()->toDateTimeString()
                    ]
                );

                $calls[] = $call;
            }

            //                Update Patient monthly sum
            $sum = collect($pageTimersForWeekForNurse)->sum('billable_duration');
            $numberOfSuccessfulCalls = collect($calls)->where('status', '=', 'reached')
                ->where('inbound_cpm_id', '=', $patient->id)->count();
            PatientMonthlySummary::updateOrCreate(
                [
                    'patient_id' => $patient->id,
                ],
                [
                    'total_time' => $sum,
                    'ccm_time' => $sum,
                    'month_year' => $date->copy()->startOfMonth()->toDateString(),
                    'no_of_calls' => collect($calls)->count(),
                    'no_of_successful_calls' => $numberOfSuccessfulCalls,
                    'actor_id' => $user->id,
                ]
            );
        }
    }

}
