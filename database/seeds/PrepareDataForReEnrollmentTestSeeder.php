<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\EnrollableManagement;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
    use EnrollableManagement;
    use SeedEligibilityJobsForEnrollees;
    use UserHelpers;

    const CCM_STATUS_UNREACHABLE = 'unreachable';

    public function createEnrollee(Practice $practice, ?string $phoneTester = null, ?string $emailTester = null)
    {
        $faker = Factory::create();

        $enrolleeForTesting = factory(Enrollee::class, 1)->create([
            'practice_id'             => $practice->id,
            'dob'                     => \Carbon\Carbon::parse('1901-01-01'),
            'referring_provider_name' => 'Dr. Demo',
            'primary_phone'           => $phoneTester,
            'home_phone'              => $phoneTester,
            'email'                   => $faker->unique()->safeEmail,
        ]);
        $this->seedEligibilityJobs(collect($enrolleeForTesting));
//                        Emulating Constantinos dashboard Importing - Mark Enrollees to invite.
        $enrolleeForTesting->first()->update([
            'status' => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);
        $this->updateEnrolleeSurveyStatuses($enrolleeForTesting->first()->id);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $phoneTester = AppConfig::pull('tester_phone', null) ?? config('services.tester.phone');
        $emailTester = AppConfig::pull('tester_email', null) ?? config('services.tester.email');

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
            $this->createEnrollee($practice, $phoneTester, $emailTester);
            ++$n;
        }

//        $n     = 1;
//        $limit = 5;
//        while ($n <= $limit) {
//            $user = $this->createUser($practice->id, 'participant', self::CCM_STATUS_UNREACHABLE);
//            $user->phoneNumbers()->update(['number' => $phoneTester]);
//            $user->update(['email' => $faker->unique()->safeEmail]);
//            $user->patientInfo()->update([
//                'birth_date'       => \Carbon\Carbon::parse('1901-01-01'),
//                'date_unreachable' => now(),
//            ]);
//            ++$n;
////            There is PatientObesrver
//        }
    }
}
