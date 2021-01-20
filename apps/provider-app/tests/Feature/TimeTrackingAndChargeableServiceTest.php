<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\OfflineActivityTimeRequest;

class TimeTrackingAndChargeableServiceTest extends TestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    public function test_activity_created_with_bhi_cs()
    {
        $practice = $this->setupPractice(true, true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice, true);

        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::BHI);

        $this->addTime($nurse, $patient, 15, true, false, $cs->id);
        /** @var Activity $activity */
        $activity = $patient->activities->first();

        self::assertEquals($activity->chargeable_service_id, $cs->id);
    }

    public function test_activity_created_with_ccm_cs()
    {
        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::CCM);

        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 15, true, false, $cs->id);

        /** @var Activity $activity */
        $activity = $patient->activities->first();

        self::assertEquals($activity->chargeable_service_id, $cs->id);
    }

    public function test_activity_created_with_pcm_cs()
    {
        $practice = $this->setupPractice(true, true, true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice, false, true);

        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::PCM);

        $this->addTime($nurse, $patient, 15, true, false, $cs->id);
        /** @var Activity $activity */
        $activity = $patient->activities->first();
        self::assertEquals($cs->id, $activity->chargeable_service_id);
        self::assertEquals(15 * 60, $activity->duration);
    }

    public function test_manual_activity_has_ccm_cs()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);

        $resp = $this->actingAs($nurse)
            ->post(route('offline-activity-time-requests.store', ['patientId' => $patient->id]), [
                'type'                  => 'Other',
                'comment'               => 'test',
                'duration_minutes'      => 3,
                'patient_id'            => $patient->id,
                'performed_at'          => now(),
                'chargeable_service_id' => ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id,
            ]);

        self::assertTrue($resp->status() < 400);

        /** @var OfflineActivityTimeRequest $request */
        $request = OfflineActivityTimeRequest::first();
        $request->approve();

        /** @var Activity $activity */
        $activity = $patient->activities->first();
        self::assertNotNull($activity);
        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::CCM);
        self::assertEquals($activity->chargeable_service_id, $cs->id);
        self::assertEquals($activity->duration, 3 * 60);
    }

    private function getNurse(int $practiceId, $enableCcmPlus)
    {
        $nurse = $this->createUser($practiceId, 'care-center');

        return $this->setupNurse($nurse, true, 20.0, $enableCcmPlus, 12.50);
    }
}
