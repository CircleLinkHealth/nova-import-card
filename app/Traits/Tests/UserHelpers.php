<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Call;
use App\CLH\Repositories\UserRepository;
use App\Repositories\PatientWriteRepository;
use Carbon\Carbon;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Factory;
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
            $user->carePlan()->updateOrCreate(
                [
                    'care_plan_template_id' => \CircleLinkHealth\Core\Entities\AppConfig::pull('default_care_plan_template_id'),
                ],
                [
                    'status' => 'draft',
                ]
            );
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

                'day_of_week' => Carbon::parse($st->toDateString())->dayOfWeek,

                'nurse_info_id' => $nurse->id,
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
        return (app(PatientWriteRepository::class))->updateCallLogs($patient, true);
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
                'email'        => $email,
                'password'     => 'password',
                'display_name' => "$firstName $lastName",
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'username'     => $faker->userName,
                'program_id'   => $practiceId,
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
            ]
        );

        //create a user
        $user = (new UserRepository())->createNewUser(new User(), $bag);

        $practice = Practice::with('locations')->findOrFail($practiceId);

        $locations = $practice->locations
            ->pluck('id')
            ->all();

        $user->locations()->sync($locations);

        return $user;
    }
}
