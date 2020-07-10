<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Location;
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
        $provider = \CircleLinkHealth\Customer\Entities\User::ofType('provider')
            ->ofPractice($practice->id)
            ->first();
        if ( ! $provider) {
            $provider = $this->createUser($practice->id, 'provider');
        }
        $enrolleeForTesting = factory(Enrollee::class)->create(array_merge($args, [
            'provider_id'             => $provider->id,
            'practice_id'             => $practice->id,
            'referring_provider_name' => $provider->getFullName(),
            // UserRepository will create a unique fake email
            'email' => '',
        ]));
        $this->seedEligibilityJobs(collect([$enrolleeForTesting]), $practice);

        // Emulating Constantinos dashboard Importing - Mark Enrollees to invite.
        $enrolleeForTesting->update([
            'status' => Enrollee::QUEUE_AUTO_ENROLLMENT,
        ]);
        $enrolleeForTesting->status = Enrollee::QUEUE_AUTO_ENROLLMENT;

        return $enrolleeForTesting->fresh('user.billingProvider');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $phoneTester = AppConfig::pull('tester_phone', null) ?? config('services.tester.phone');

        $practice = Practice::firstOrCreate(
            [
                'name' => 'demo',
            ],
            [
                'active'                => 1,
                'display_name'          => 'Demo Clinic',
                'is_demo'               => 1,
                'clh_pppm'              => 0,
                'term_days'             => 30,
                'outgoing_phone_number' => 2025550196,
            ]
        );

        $location = Location::firstOrCreate(
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
