<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;

class CreateSelfEnrollmentTestData extends Command
{
    use SeedEligibilityJobsForEnrollees;
    use UserHelpers;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Triggers 'PrepareDataForReEnrollmentTestSeeder' while passing parameters";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:selfEnrollmentTestData {practiceName}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
     * Execute the console command.
     *
     * @throws \Exception
     *
     * @return int
     */
    public function handle()
    {
        $practiceName = $this->argument('practiceName') ?? null;

        if (isProductionEnv()) {
            throw new \Exception('You cannot execute this action in production environment');
        }

        if (is_null($practiceName)) {
            throw new \Exception('Practice input is required');
        }

        $phoneTester = AppConfig::pull('tester_phone', null) ?? config('services.tester.phone');

        $practice = Practice::firstOrCreate(
            [
                'name' => $practiceName,
            ],
            [
                'active'                => 1,
                'display_name'          => ucfirst(str_replace('-', ' ', $practiceName)),
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
