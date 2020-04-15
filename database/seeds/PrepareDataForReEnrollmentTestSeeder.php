<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Patient;
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
        $faker    = Factory::create();
        $practice = Practice::firstOrCreate(
            [
                'name' => 'demo',
            ],
            [
                'active'                => 1,
                'display_name'          => 'Demo',
                'is_demo'               => 1,
                'clh_pppm'              => 0,
                'term_days'             => 30,
                'outgoing_phone_number' => +18886958537,
            ]
        );
        $mothStart = Carbon::parse(now())->copy()->startOfMonth()->toDateTimeString();
        $monthEnd  = Carbon::parse($mothStart)->copy()->endOfMonth()->toDateTimeString();
        $provider  = $this->createUser($practice->id, 'provider', 'enrolled');

        $enrollees = Enrollee::where('dob', \Carbon\Carbon::parse('1901-01-01'))
            ->whereDoesntHave('enrollmentInvitationLink');

        if ( ! $enrollees->exists() || $enrollees->count() < 5) {
            $n     = 1;
            $limit = 5;
            while ($n <= $limit) {
                Enrollee::create(
                    [
                        'provider_id'             => $provider->id,
                        'practice_id'             => $practice->id,
                        'mrn'                     => $faker->randomNumber(6),
                        'first_name'              => $faker->firstName,
                        'last_name'               => $faker->lastName,
                        'address'                 => $faker->address,
                        'city'                    => $faker->city,
                        'state'                   => $faker->state,
                        'zip'                     => 44508,
                        'primary_phone'           => $faker->phoneNumber,
                        'other_phone'             => $faker->phoneNumber,
                        'home_phone'              => $faker->phoneNumber,
                        'cell_phone'              => $faker->phoneNumber,
                        'dob'                     => \Carbon\Carbon::parse('1901-01-01'),
                        'lang'                    => 'EN',
                        'status'                  => Enrollee::TO_CALL,
                        'primary_insurance'       => 'test',
                        'secondary_insurance'     => 'test',
                        'email'                   => $faker->email,
                        'referring_provider_name' => 'Dr. Demo',
                    ]
                );

                ++$n;
            }
        }

        $unreachablePatients = User::with('patientInfo')
            ->where('program_id', $practice->id)
            ->whereDoesntHave('enrollmentInvitationLink')
            ->whereHas('patientInfo', function ($patient) use ($mothStart, $monthEnd) {
                // @var Patient $patient
                $patient->where('ccm_status', self::CCM_STATUS_UNREACHABLE)->where([
                    ['date_unreachable', '>=', $mothStart],
                    ['date_unreachable', '<=', $monthEnd],
                ])->where('birth_date', '=', '1901-01-01');
            });

        if ( ! $unreachablePatients->exists() || $unreachablePatients->count() < 5) {
            $n     = 1;
            $limit = 5;
            while ($n <= $limit) {
                $user = $this->createUser($practice->id, 'participant', self::CCM_STATUS_UNREACHABLE);
                $user->patientInfo()->update([
                    'birth_date'       => \Carbon\Carbon::parse('1901-01-01'),
                    'date_unreachable' => now(),
                ]);
                ++$n;
            }
        }
    }
}
