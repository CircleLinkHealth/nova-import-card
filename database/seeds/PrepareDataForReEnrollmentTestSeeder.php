<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
//    We can create UI for tester to choose for which practice to create patients
    use SeedEligibilityJobsForEnrollees;
    use UserHelpers;

    const CCM_STATUS_UNREACHABLE = 'unreachable';
    /**
     * @var string
     */
    private $practiceName;

    /**
     * PrepareDataForReEnrollmentTestSeeder constructor.
     *
     * @param string $practiceName
     */
    public function __construct(string $practiceName = null)
    {
        if (\Illuminate\Support\Facades\App::environment(['local', 'testing', 'review']) && is_null($practiceName)) {
            $this->practiceName = 'demo-clinic';
        } else {
            $this->practiceName = $practiceName;
        }
    }

    public function createEnrollee(Practice $practice, User $provider, array $args = [])
    {
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
                'name' => $this->practiceName ?? 'test',
            ],
            [
                'active'                => 1,
                'display_name'          => ucfirst(str_replace('-', ' ', $this->practiceName)),
                'is_demo'               => 1,
                'clh_pppm'              => 0,
                'term_days'             => 30,
                'outgoing_phone_number' => 2025550196,
                'saas_account_id'       => SaasAccount::whereName('CircleLink Health')->first()->id,
            ]
        );

        $location = Location::firstOrCreate(
            [
                'practice_id' => $practice->id,
            ],
            [
                'is_primary'     => 1,
                'name'           => $practice->name,
                'address_line_1' => '84982 This is demo Address, AZ 58735-9955',
                'city'           => 'West Guantanamo Demo World',
                'state'          => 'MD',
                'postal_code'    => '21335 - 9764',
            ]
        );

        $provider = User::ofType('provider')
            ->with('providerInfo')
            ->ofPractice($practice->id)
            ->first();
        if ( ! $provider) {
            $provider = $this->createUser($practice->id, 'provider');
        }

        $provider->providerInfo->update([
            // This is a real npi number of a real provider.
            // We need this to display signature in letter.
            'npi_number' => 1962409979,
        ]);

        $n       = 1;
        $limit   = 5;
        $testDob = \Carbon\Carbon::parse('1901-01-01');
        while ($n <= $limit) {
            $this->createEnrollee($practice, $provider, [
                'primary_phone' => $phoneTester,
                'home_phone'    => $phoneTester,
                'cell_phone'    => $phoneTester,
                'dob'           => $testDob,
                'location_id'   => $location->id,
            ]);
            ++$n;
        }
    }
}
