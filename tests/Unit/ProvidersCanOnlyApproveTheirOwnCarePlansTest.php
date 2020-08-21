<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Tests\CustomerTestCase;

class ProvidersCanOnlyApproveTheirOwnCarePlansTest extends CustomerTestCase
{
    const FORWARDS_COUNT     = 3;
    const NOT_FORWARDS_COUNT = 2;

    public function test_patients_pending_provider_approval_query()
    {
        [$doesntForward, $forwards] = $this->provider(2);
        $other                      = $this->careCoach();

        $this->createPatients($forwards, self::FORWARDS_COUNT);
        $this->createPatients($doesntForward, self::NOT_FORWARDS_COUNT);

        self::assertCanOnlyViewOwnPatientCarePlans($forwards);
        self::assertSeesCarePlans($forwards, self::FORWARDS_COUNT);

        self::assertCanOnlyViewOwnPatientCarePlans($doesntForward);
        self::assertSeesCarePlans($doesntForward, self::NOT_FORWARDS_COUNT);

        self::assertSeesCarePlans($other, 0);
        $this->setUpForwarding($forwards, $other);
        self::assertSeesCarePlansForwardedBy($other, $forwards, self::FORWARDS_COUNT);

        $this->allowViewingAllPracticeCarePlans($forwards);
        self::assertSeesAllPracticeCarePlans($forwards);
    
        $this->allowViewingAllPracticeCarePlans($doesntForward);
        self::assertSeesAllPracticeCarePlans($doesntForward);
    }

    private function allowViewingAllPracticeCarePlans(User &$provider)
    {
        $provider->providerInfo->approve_own_care_plans = 0;
        $provider->providerInfo->save();
    }

    private static function assertCanOnlyViewOwnPatientCarePlans(User $provider)
    {
        self::assertTrue($provider->providerInfo->approve_own_care_plans);
    }

    private static function assertSeesAllPracticeCarePlans(User $provider)
    {
        self::assertEquals(
            self::FORWARDS_COUNT + self::NOT_FORWARDS_COUNT,
            self::query($provider)->count()
        );
    }

    private static function assertSeesCarePlans(User $user, int $count)
    {
        $query = self::query($user);
        self::assertEquals($count, $query->count());

        if ( ! $user->isProvider()) {
            return;
        }

        foreach ($query->get() as $patient) {
            self::assertEquals($user->id, $patient->getBillingProviderId());
        }
    }

    private static function assertSeesCarePlansForwardedBy(User $receiver, User $provider, int $count)
    {
        $query = self::query($receiver);
        self::assertEquals($count, $query->count());

        foreach ($query->get() as $patient) {
            self::assertEquals($provider->id, $patient->getBillingProviderId());
        }
    }

    private function createPatients(User $provider, int $count)
    {
        foreach ($this->createUsersOfType('participant', $count) as $patient) {
            CarePerson::updateOrCreate([
                'user_id' => $patient->id,
                'type'    => CarePerson::BILLING_PROVIDER,
            ], [
                'member_user_id' => $provider->id,
            ]);

            CarePlan::updateOrCreate([
                'user_id' => $patient->id,
            ], [
                'status' => CarePlan::RN_APPROVED,
            ]);
        }
    }

    private static function query(User $user)
    {
        return User::patientsPendingProviderApproval($user);
    }

    private function setUpForwarding(User $forwards, User $receives)
    {
        $forwards->forwardAlertsTo()->attach($receives->id, [
            'name' => User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER,
        ]);
    }
}
