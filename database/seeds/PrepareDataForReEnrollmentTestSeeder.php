<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
//    We can create UI for tester to choose for which practice to create patients
    use SeedEligibilityJobsForEnrollees;
    use UserHelpers;

    const CCM_STATUS_UNREACHABLE = 'unreachable';

    public function createEnrollee(Practice $practice, array $args = [])
    {
        $enrolleeForTesting = factory(Enrollee::class)->create(array_merge($args, [
            'practice_id'             => $practice->id,
            'referring_provider_name' => 'Dr. Demo',
            'email'                   => '',
        ]));
        $this->seedEligibilityJobs(collect([$enrolleeForTesting]), $practice);
//                        Emulating Constantinos dashboard Importing - Mark Enrollees to invite.
        $enrolleeForTesting->update([
            'status' => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);
        $enrolleeForTesting->status = Enrollee::QUEUE_AUTO_ENROLLMENT;

        return $enrolleeForTesting->fresh('user.billingProvider');
    }

    public function createSurveyConditions(int $userId, int $surveyInstanceId, int $surveyId, string $status)
    {
        DB::table('users_surveys')->insert(
            [
                'user_id'            => $userId,
                'survey_instance_id' => $surveyInstanceId,
                'survey_id'          => $surveyId,
                'status'             => $status,
                'start_date'         => Carbon::parse(now())->toDateTimeString(),
            ]
        );
    }

    public function createSurveyConditionsAndGetSurveyInstance(string $userId, string $status)
    {
        $surveyId = $this->firstOrCreateEnrollmentSurvey();

        $surveyInstanceId = DB::table('survey_instances')->insertGetId([
            'survey_id' => $surveyId,
            'year'      => Carbon::now(),
        ]);

        self::createSurveyConditions($userId, $surveyInstanceId, $surveyId, $status);

        return DB::table('survey_instances')->where('id', '=', $surveyInstanceId)->first();
    }

    public function firstOrCreateEnrollmentSurvey()
    {
        $surveyId = optional(DB::table('surveys')
            ->where('name', SelfEnrollmentController::ENROLLEES_SURVEY_NAME)
            ->first())->id;

        if ( ! $surveyId) {
            $surveyId = DB::table('surveys')
                ->insertGetId([
                    'name' => SelfEnrollmentController::ENROLLEES_SURVEY_NAME,
                ]);
        }

        DB::table('survey_instances')->insertGetId([
            'survey_id' => $surveyId,
            'year'      => now()->year,
        ]);

        return $surveyId;
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
                'name' => 'toledo-demo',
            ],
            [
                'active'                => 1,
                'display_name'          => 'Toledo Demo',
                'is_demo'               => 1,
                'clh_pppm'              => 0,
                'term_days'             => 30,
                'outgoing_phone_number' => 2025550196,
            ]
        );

        $n       = 1;
        $limit   = 5;
        $testDob = \Carbon\Carbon::parse('1901-01-01');
        while ($n <= $limit) {
            $enrollee = $this->createEnrollee($practice, [
                'primary_phone' => $phoneTester,
                'home_phone'    => $phoneTester,
                'cell_phone'    => $phoneTester,
                'dob'           => $testDob,
            ]);
            $location = $enrollee->location()->firstOrCreate(
                [
                    'practice_id' => $practice->id,
                ],
                [
                    'is_primary'     => 1,
                    'name'           => $practice->name,
                    'address_line_1' => '84982 Sipes Manor Theoborough, AZ 58735-9955',
                    'city'           => 'West Jeraldbury',
                    'state'          => 'MD',
                    'postal_code'    => '21335 - 9764',
                ]
            );
            $enrollee->update(
                [
                    'location_id' => $location->id,
                ]
            );

            $enrollee->provider->providerInfo->update([
                //                This is a real npi number of a real provider. We need this to display signature in letter.
                'npi_number' => 1962409979,
            ]);
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
