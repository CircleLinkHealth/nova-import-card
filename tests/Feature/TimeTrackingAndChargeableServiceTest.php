<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\TimeHelpers;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\OfflineActivityTimeRequest;
use Tests\CustomerTestCase;

class TimeTrackingAndChargeableServiceTest extends CustomerTestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    public function test_activity_created_with_bhi_cs()
    {
        $practice = $this->setupPractice(true, true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice, true);
        $this->addTime($nurse, $patient, 15, true, false, true);
        /** @var Activity $activity */
        $activity = $patient->activities->first();
        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::BHI);
        self::assertEquals($activity->chargeable_service_id, $cs->id);
    }

    public function test_activity_created_with_ccm_cs()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 15, true, false);
        /** @var Activity $activity */
        $activity = $patient->activities->first();
        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::CCM);
        self::assertEquals($activity->chargeable_service_id, $cs->id);
    }

    public function test_activity_created_with_ccm_plus_40_cs()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 15, true, false);
        $this->addTime($nurse, $patient, 15, true, false);
        $sum = $patient->activities->sum(function (Activity $activity) {
            return $activity->duration;
        });
        self::assertEquals($sum, 30 * 60);

        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::CCM_PLUS_40);
        /** @var Activity $activity */
        $activity = $patient->activities()->where('chargeable_service_id', '=', $cs->id)->first();
        self::assertNotNull($activity);
        self::assertEquals(10 * 60, $activity->duration);
    }

    public function test_activity_created_with_ccm_plus_60_cs()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 15, true, false);
        $this->addTime($nurse, $patient, 15, true, false);
        $this->addTime($nurse, $patient, 15, true, true);
        $sum = $patient->activities->sum(function (Activity $activity) {
            return $activity->duration;
        });
        self::assertEquals($sum, 45 * 60);

        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::CCM_PLUS_60);
        /** @var Activity $activity */
        $activity = $patient->activities()->where('chargeable_service_id', '=', $cs->id)->first();
        self::assertNotNull($activity);
        self::assertEquals(5 * 60, $activity->duration);
    }

    public function test_activity_created_with_pcm_cs()
    {
        $practice = $this->setupPractice(true, true, true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice, false, true);
        $this->addTime($nurse, $patient, 15, true, false);
        /** @var Activity $activity */
        $activity = $patient->activities->first();
        /** @var ChargeableService $cs */
        $cs = ChargeableService::firstWhere('code', '=', ChargeableService::PCM);
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
                'type'             => 'Other',
                'comment'          => 'test',
                'duration_minutes' => 3,
                'patient_id'       => $patient->id,
                'is_behavioral'    => 0,
                'performed_at'     => now(),
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
