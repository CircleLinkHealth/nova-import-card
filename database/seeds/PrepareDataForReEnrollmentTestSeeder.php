<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
    use SeedEligibilityJobsForEnrollees;
    use UserHelpers;

    const CCM_STATUS_UNREACHABLE = 'unreachable';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
                'outgoing_phone_number' => 2025550196,
            ]
        );

        $n     = 1;
        $limit = 5;
        while ($n <= $limit) {
            $enrolleesForTesting = factory(Enrollee::class, 1)->create([
                'practice_id'             => $practice->id,
                'dob'                     => \Carbon\Carbon::parse('1901-01-01'),
                'referring_provider_name' => 'Dr. Demo',
                'mrn'                     => mt_rand(100000, 999999),
                'primary_phone'           => config('services.tester.phone'),
                'home_phone'              => config('services.tester.phone'),
                'email'                   => config('services.tester.email'),
            ]);
            $this->seedEligibilityJobs(collect($enrolleesForTesting));
            ++$n;
        }

        $n     = 1;
        $limit = 5;
        while ($n <= $limit) {
            $user = $this->createUser($practice->id, 'participant', self::CCM_STATUS_UNREACHABLE);
            $user->phoneNumbers()->update(['number' => config('services.tester.phone')]);
            $user->update(['email' => config('services.tester.email')]);
            $user->patientInfo()->update([
                'birth_date'       => \Carbon\Carbon::parse('1901-01-01'),
                'date_unreachable' => now(),
            ]);
            ++$n;
        }
    }
}
