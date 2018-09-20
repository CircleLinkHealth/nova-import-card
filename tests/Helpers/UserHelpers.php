<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 14/11/2016
 * Time: 4:01 PM
 */

namespace Tests\Helpers;

use App\Call;
use App\CLH\Repositories\UserRepository;
use App\Facades\StringManipulation;
use App\Nurse;
use App\NurseContactWindow;
use App\Patient;
use App\PatientContactWindow;
use App\Practice;
use App\Repositories\PatientWriteRepository;
use App\Role;
use App\User;
use Carbon\Carbon;
use Faker\Factory;
use Symfony\Component\HttpFoundation\ParameterBag;

trait UserHelpers
{
    /**
     * @param int $practiceId
     * @param string $roleName
     *
     * @return User
     */
    public function createUser(
        $practiceId = 8,
        $roleName = 'provider'
    ): User {

        $roles = [
            Role::whereName($roleName)->firstOrFail()->id,
        ];

        //creates the User
        $user = $this->setupUser($practiceId, $roles);


        $email = $user->email;
        $locations = $user->locations->pluck('id')->all();

        foreach ($locations as $locId) {
            $this->assertDatabaseHas('location_user', [
                'location_id' => $locId,
                'user_id'     => $user->id,
            ]);
        }

        //check that it was created
        $this->assertDatabaseHas('users', ['email' => $email]);

        //check that the roles were created
        foreach ($roles as $role) {
            $is_admin = $role == 1;
            $user->attachPractice($practiceId, $is_admin, $is_admin, $role);
            $this->assertDatabaseHas('practice_role_user', [
                'user_id'    => $user->id,
                'role_id'    => $role,
                'program_id' => $practiceId,
            ]);
        }

        if ($roleName == 'participant') {
            $user->carePlan()->create([
                'status' => 'draft',
            ]);

            $user->patientInfo()->create();
        }

        $user->load('practices');

        return $user;
    }

    public function setupUser($practiceId, $roles){
        $faker = Factory::create();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $email     = $faker->email;
        $workPhone = StringManipulation::formatPhoneNumber($faker->phoneNumber);


        $bag = new ParameterBag([
            'email'             => $email,
            'password'          => 'password',
            'display_name'      => "$firstName $lastName",
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'username'          => $faker->userName,
            'program_id'        => $practiceId,
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
            'prefix'            => 'Dr',
            'suffix'            => 'MD',
            'npi_number'        => 1234567890,
            'specialty'         => 'Unit Tester',

            //phones
            'home_phone_number' => $workPhone,

            'ccm_status' => 'enrolled'
        ]);

        //create a user
        $user = (new UserRepository())->createNewUser(new User(), $bag);

        $practice = Practice::find($practiceId);

        $locations = $practice->locations
            ->pluck('id')
            ->all();

        $user->locations()->sync($locations);

        return $user;
    }

    public function createLastCallForPatient(
        Patient $patient,
        Nurse $scheduler
    ) {

        $call = Call::create([

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
            'window_start'   => '09:00:00',
            'window_end'     => '10:00:00',

            'is_cpm_outbound' => true,

        ]);

        return $call;
    }

    public function createWindowForNurse(
        Nurse $nurse,
        Carbon $st,
        Carbon $end
    ) {

        $window = timestampsToWindow($st, $end);

        $res = NurseContactWindow::create([

            'date'              => $st->toDateString(),
            'window_time_start' => $window['start'],
            'window_time_end'   => $window['end'],

            'day_of_week' => 5,

            'nurse_info_id' => $nurse->id,

        ]);

        return $res;
    }

    //NURSE TEST HELPERS

    public function createWindowForPatient(
        Patient $patient,
        Carbon $st,
        Carbon $end,
        $dayOfWeek
    ) {

        $window = timestampsToWindow($st, $end);

        return PatientContactWindow::create([

            'window_time_start' => $window['start'],
            'window_time_end'   => $window['end'],

            'day_of_week' => $dayOfWeek,

            'patient_info_id' => $patient->id,

        ]);
    }

    public function makePatientMonthlyRecord(Patient $patient)
    {

        return (app(PatientWriteRepository::class))->updateCallLogs($patient, true);
    }
}
