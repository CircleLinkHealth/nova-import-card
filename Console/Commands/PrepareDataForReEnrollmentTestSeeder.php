<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter;
use CircleLinkHealth\SelfEnrollment\Domain\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
    use SeedEligibilityJobsForEnrollees;
    use UserHelpers;

    const CCM_STATUS_UNREACHABLE = 'unreachable';
    private int $countRandomProvider;

    /**
     * @var string
     */
    private $practiceName;
    /**
     * @var \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|int|mixed
     */
    private $skipIds;
    /**
     * @var \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|int|mixed
     */
    private $uiRequestedProviders;
    /**
     * @var null
     */
    private $uiRequestsForThisPractice;

    /**
     * PrepareDataForReEnrollmentTestSeeder constructor.
     *
     * @param null $uiRequestsForThisPractice
     */
    public function __construct(string $practiceName = null, $uiRequestsForThisPractice = null)
    {
        if (\Illuminate\Support\Facades\App::environment(['local', 'testing', 'review']) && is_null($practiceName)) {
            $this->practiceName = 'demo-clinic';
        } else {
            $this->practiceName = $practiceName;
        }

        $this->uiRequestsForThisPractice = $uiRequestsForThisPractice;
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

        $practice = $this->selfEnrollmentTestPractice($this->practiceName);

        $location = $this->selfEnrollmentTestLocation($practice->id, $practice->name);

        $n       = 1;
        $limit   = 5;
        $testDob = \Carbon\Carbon::parse('1901-01-01');

        $provider                   = null;
        $this->countRandomProvider  = 0;
        $this->uiRequestedProviders = collect();
        $this->skipIds              = collect();

        $provider = $this->randomProvider($practice->id);

        while ($n <= $limit) {
            if (0 === $this->countRandomProvider && EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER === $this->uiRequestsForThisPractice) {
                $provider->providerInfo->update([
                    // We need this just for Toledo.
                    'npi_number' => 1962409979,
                ]);

                $this->countRandomProvider = 1;
            }

            if (EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER_GROUP === $this->uiRequestsForThisPractice) {
                if ($this->uiRequestedProviders->isEmpty()) {
                    $providers = $this->getUiRequestedProviders($practice->id);
                    $this->uiRequestedProviders->push(...$providers);
                }
                $provider = $this->filterProvider();
            }

            $enrollee = $this->createEnrollee($practice, $provider, [
                'primary_phone' => $phoneTester,
                'home_phone'    => $phoneTester,
                'cell_phone'    => $phoneTester,
                'dob'           => $testDob,
                'location_id'   => $location->id,
            ]);

            if (is_null($enrollee->user_id)) {
                CreateSurveyOnlyUserFromEnrollee::execute($enrollee);
            }

            $this->assignSpecificBillingProvider($enrollee->user_id, $provider->id);

            ++$n;
        }
    }

    private function assignSpecificBillingProvider(int $enrolleeUserId, int $providerId)
    {
        CarePerson::firstOrCreate([
            'user_id'        => $enrolleeUserId,
            'member_user_id' => $providerId,
            'type'           => CarePerson::BILLING_PROVIDER,
            'alert'          => 1,
        ]);
    }

    private function filterProvider()
    {
        $user = $this->uiRequestedProviders->whereNotIn('id', $this->skipIds->toArray())->first();
        $this->skipIds->push($user->id);

        return $user;
    }

    private function getUiRequestedProviders(int $practiceId)
    {
        return User::with('providerInfo')
            ->whereHas('providerInfo')
            ->where('program_id', $practiceId)
            ->get();
    }

    private function randomProvider(int $practiceId)
    {
        $provider = User::ofType('provider')
            ->with('providerInfo')
            ->whereHas('providerInfo')
            ->ofPractice($practiceId)
            ->first();

        if ( ! $provider) {
            $provider = $this->createUser($practiceId, 'provider');
        }

        return $provider;
    }
}
