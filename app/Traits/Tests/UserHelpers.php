<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Faker\Factory;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;

trait UserHelpers
{
    public function createLastCallForPatient(
        Patient $patient,
        Nurse $scheduler
    ) {
        return Call::create(
            [
                'service'     => 'phone',
                'status'      => 'not reached',
                'called_date' => '2016-07-16',

                'attempt_note' => '',

                'scheduler' => $scheduler->user->id,

                'inbound_phone_number' => '111-111-1111',

                'outbound_phone_number' => '',

                'inbound_cpm_id'  => $patient->user->id,
                'outbound_cpm_id' => $scheduler->user->id,

                'call_time'  => 0,
                'created_at' => Carbon::now()->toDateTimeString(),

                'scheduled_date' => '2016-12-01',
                'window_start'   => '09:00',
                'window_end'     => '10:00',

                'is_cpm_outbound' => true,
            ]
        );
    }

    /**
     * @param int    $practiceId
     * @param string $roleName
     * @param mixed  $ccmStatus
     */
    public function createUser(
        $practiceId = 8,
        $roleName = 'provider',
        $ccmStatus = 'enrolled'
    ): User {
        $practiceId = parseIds($practiceId)[0];
        $roles      = [Role::whereName($roleName)->firstOrFail()->id];

        //creates the User
        $user = $this->setupUser($practiceId, $roles, $ccmStatus);

        $email     = $user->email;
        $locations = $user->locations->pluck('id')->all();

        $isTest = method_exists($this, 'assertDatabaseHas');

        if ($isTest) {
            foreach ($locations as $locId) {
                $this->assertDatabaseHas(
                    'location_user',
                    [
                        'location_id' => $locId,
                        'user_id'     => $user->id,
                    ]
                );
            }
        }

        if ($isTest) {
            //check that it was created
            $this->assertDatabaseHas('users', ['email' => $email]);
        }

        //check that the roles were created
        foreach ($roles as $role) {
            $is_admin = 1 == $role;
            $user->attachPractice($practiceId, [$role], $is_admin);
            if ($isTest) {
                $this->assertDatabaseHas(
                    'practice_role_user',
                    [
                        'user_id'    => $user->id,
                        'role_id'    => $role,
                        'program_id' => $practiceId,
                    ]
                );
            }
        }

        if ('participant' == $roleName) {
            $user->ccdMedications()->create([
                'name' => 'Test Aspirin',
            ]);
            CarePlan::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'care_plan_template_id' => getDefaultCarePlanTemplate()->id,
                    'status'                => 'draft',
                ]
            );
            $this->makePatientMonthlyRecord($user->patientInfo);
        }

        $user->load(['practices', 'patientInfo', 'carePlan']);

        return $user;
    }

    public function createWindowForNurse(
        Nurse $nurse,
        Carbon $st,
        Carbon $end
    ) {
        $window = timestampsToWindow($st, $end);

        return NurseContactWindow::create(
            [
                'date'              => $st->toDateString(),
                'window_time_start' => $window['start'],
                'window_time_end'   => $window['end'],
                'repeat_frequency'  => 'does_not_repeat',
                'day_of_week'       => Carbon::parse($st->toDateString())->dayOfWeek,
                'nurse_info_id'     => $nurse->id,
            ]
        );
    }

    //NURSE TEST HELPERS

    public function createWindowForPatient(
        Patient $patient,
        Carbon $st,
        Carbon $end,
        $dayOfWeek
    ) {
        $window = timestampsToWindow($st, $end);

        return PatientContactWindow::create(
            [
                'window_time_start' => $window['start'],
                'window_time_end'   => $window['end'],

                'day_of_week' => $dayOfWeek,

                'patient_info_id' => $patient->id,
            ]
        );
    }

    public function makePatientMonthlyRecord(Patient $patient)
    {
        return PatientMonthlySummary::updateOrCreate(
            [
                'month_year' => now()->startOfMonth()->toDateString(),
                'patient_id' => $patient->user_id,
            ],
            [
                'ccm_time'                            => 0,
                'bhi'                                 => 0,
                'no_of_calls'                         => 0,
                'no_of_successful_calls'              => 0,
                'no_call_attempts_since_last_success' => 0,
            ]
        );
    }

    public function setupUser($practiceId, $roles, $ccmStatus = 'enrolled')
    {
        $faker = Factory::create();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $email     = $faker->email;
        $workPhone = (new StringManipulation())->formatPhoneNumber($faker->phoneNumber);

        $bag = new ParameterBag(
            [
                'saas_account_id' => SaasAccount::firstOrFail()->id,
                'email'           => $email,
                'password'        => 'password',
                'display_name'    => "$firstName $lastName",
                'first_name'      => $firstName,
                'last_name'       => $lastName,
                'username'        => $faker->userName,
                'program_id'      => $practiceId,
                //id=9 is testdrive
                'address'           => $faker->streetAddress,
                'user_status'       => 1,
                'address2'          => '',
                'city'              => $faker->city,
                'state'             => 'AL',
                'zip'               => '12345',
                'is_auto_generated' => true,
                'roles'             => $roles,
                'timezone'          => 'America/New_York',

                //provider Info
                'prefix'     => 'Dr',
                'suffix'     => 'MD',
                'npi_number' => 1234567890,
                'specialty'  => 'Unit Tester',

                //phones
                'home_phone_number' => $workPhone,

                'ccm_status' => $ccmStatus,
                'birth_date' => $faker->date('Y-m-d'),
            ]
        );

        //create a user
        $user = (new UserRepository())->createNewUser($bag);

        $practice = Practice::with('locations')->findOrFail($practiceId);

        $locations = $practice->locations
            ->pluck('id')
            ->all();

        $user->locations()->sync($locations);

        if (array_key_exists(0, $locations) && is_numeric($locations[0])) {
            $user->setPreferredContactLocation($locations[0]);
        }

        return $user;
    }

    private function addWorkHours(User $nurse, Carbon $forDate, int $hours)
    {
        $workWeekStart = $forDate->copy()->startOfWeek()->toDateString();
        $dayOfWeek     = carbonToClhDayOfWeek($forDate->dayOfWeek);

        $nurse->nurseInfo->windows()->updateOrCreate(
            [
                'date' => $forDate->toDateString(),
            ],
            [
                'day_of_week'       => $dayOfWeek,
                'window_time_start' => '11:00',
                'window_time_end'   => '18:00',
                'repeat_frequency'  => 'does_not_repeat',
            ]
        );

        $nurse->nurseInfo->workhourables()->updateOrCreate(
            [
                'work_week_start' => $workWeekStart,
            ],
            [
                strtolower(clhDayOfWeekToDayName($dayOfWeek)) => $hours,
            ]
        );
    }

    private function setupNurse(
        User $nurse,
        bool $variableRate = true,
        float $hourlyRate = 29.0,
        bool $enableCcmPlus = false,
        float $visitFee = null,
        Carbon $startDate = null
    ) {
        if ( ! $startDate) {
            $startDate = now()->startOfDay();
        }

        $nurse->nurseInfo->start_date = $startDate;

        $nurse->nurseInfo->is_variable_rate = $variableRate;
        $nurse->nurseInfo->hourly_rate      = $hourlyRate;
        $nurse->nurseInfo->high_rate        = 30.00;
        $nurse->nurseInfo->high_rate_2      = 28.00;
        $nurse->nurseInfo->high_rate_3      = 27.50;

        $nurse->nurseInfo->low_rate = 10;

        if ($visitFee) {
            $nurse->nurseInfo->visit_fee   = $visitFee;
            $nurse->nurseInfo->visit_fee_2 = 12.00;
            $nurse->nurseInfo->visit_fee_3 = 11.75;
        }

        $nurse->nurseInfo->save();

        AppConfig::set(NurseCcmPlusConfig::NURSE_CCM_PLUS_ENABLED_FOR_ALL, $enableCcmPlus
                    ? 'true'
                    : 'false');

        //make sure this is false
        AppConfig::set(NurseCcmPlusConfig::NURSE_CCM_PLUS_ALT_ALGO_ENABLED_FOR_ALL, 'false');

        if ($enableCcmPlus && $visitFee) {
            $current = implode(',', NurseCcmPlusConfig::altAlgoEnabledForUserIds());
            AppConfig::set(NurseCcmPlusConfig::NURSE_CCM_PLUS_ALT_ALGO_ENABLED_FOR_USER_IDS, $current.(empty($current)
                            ? ''
                            : ',').$nurse->id);

            //hack for SmartCacheManager
            \Cache::store('array')->clear();
        }

        return $nurse;
    }

    private function setupPatient(Practice $practice, $isBhi = false, $pcmOnly = false)
    {
        $patient = $this->createUser($practice->id, 'participant');
        $patient->setPreferredContactLocation($this->location->id);

        if ($isBhi) {
            $consentDate = Carbon::parse(Patient::DATE_CONSENT_INCLUDES_BHI);
            $consentDate->addDay();
            $patient->patientInfo->consent_date = $consentDate;
        }

        $patient->patientInfo->ccm_status = Patient::ENROLLED;
        $patient->patientInfo->save();
        $cpmProblems = CpmProblem::get();

        //$pcmOnly means one ccm condition only
        if ($pcmOnly) {
            $ccdProblems = $patient->ccdProblems()->createMany([
                ['name' => 'test'.Str::random(5)],
            ]);
        } else {
            $ccdProblems = $patient->ccdProblems()->createMany([
                ['name' => 'test'.Str::random(5), 'is_monitored' => 1],
                ['name' => 'test'.Str::random(5)],
                ['name' => 'test'.Str::random(5)],
            ]);
        }

        $len = $ccdProblems->count();
        for ($i = 0; $i < $len; ++$i) {
            $problem = $ccdProblems->get($i);
            $isLast  = $i === $len - 1;
            if ($isLast && $isBhi) {
                $problem->cpmProblem()->associate($cpmProblems->firstWhere('is_behavioral', '=', 1));
            } else {
                $problem->cpmProblem()->associate($cpmProblems->random());
            }
            $problem->save();
        }

        return $patient;
    }
}
