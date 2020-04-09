<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
    use UserHelpers;
    const CCM_STATUS_UNREACHABLE = 'unreachable';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = Factory::create();
        $practice = Practice::firstOrCreate(
            [
                'name' => 'demo'
            ],
            [
                'active' => 1,
                'display_name' => 'Demo',
                'is_demo' => 1,
                'clh_pppm' => 0,
                'term_days' => 30,
                'outgoing_phone_number' => +18886958537
            ]
        );
        $mothStart = Carbon::parse(now())->copy()->startOfMonth()->toDateTimeString();
        $monthEnd = Carbon::parse($mothStart)->copy()->endOfMonth()->toDateTimeString();
        $provider = $this->createUser($practice->id, 'provider', 'enrolled');

        $n = 1;
        $limit = 5;
        while ($n <= $limit) {
             Enrollee::create(
                [
                    'provider_id' => $provider->id,
                    'practice_id' => $practice->id,
                    'mrn' => $faker->numberBetween(6, 6),
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'address' => $faker->address,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'zip' => 44508,
                    'primary_phone' => $faker->phoneNumber,
                    'other_phone' => $faker->phoneNumber,
                    'home_phone' => $faker->phoneNumber,
                    'cell_phone' => $faker->phoneNumber,
                    'dob' => $faker->date('Y-m-d'),
                    'lang' => 'EN',
                    'status' => Enrollee::TO_CALL,
                    'primary_insurance' => 'test',
                    'secondary_insurance' => 'test',
                    'email' => $faker->email,
                    'referring_provider_name' => 'Dr. Demo',
                ]
            );
        }

        $unreachablePatients = User::with('patientInfo')
            ->whereDoesntHave('enrollmentInvitationLink')
            ->whereHas('patientInfo', function ($patient) use ($mothStart, $monthEnd) {
                $patient->where('ccm_status', self::CCM_STATUS_UNREACHABLE)->where([
                    ['date_unreachable', '>=', $mothStart],
                    ['date_unreachable', '<=', $monthEnd],
                ]);
            })->exists();

        if (!$unreachablePatients) {
            $n = 1;
            $limit = 5;
            while ($n <= $limit) {
                $user = $this->createUser($practice->id, 'participant', self::CCM_STATUS_UNREACHABLE);
                $user->patientInfo()->update([
                    'birth_date' => $faker->date('Y-m-d'),
                    'date_unreachable' => now()
                ]);
                ++$n;
            }
        }


    }
}