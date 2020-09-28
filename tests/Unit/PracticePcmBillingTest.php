<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\CpmAdmin\Http\Resources\ApprovableBillablePatient;
use CircleLinkHealth\CpmAdmin\Services\ApproveBillablePatientsService;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\TimeHelpers;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Tests\TestCase;

class PracticePcmBillingTest extends TestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    /** @var ApproveBillablePatientsService */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ApproveBillablePatientsService::class);
    }

    public function test_pcm_charged_only()
    {
        $practice = $this->setupPractice(false, false, false, true);
        $nurse    = $this->getNurse($practice->id);
        $patient  = $this->setupPatient($practice, false, true);

        $this->addTime($nurse, $patient, 35, true, 1);

        $patients  = $this->service->getBillablePatientsForMonth($practice->id, now());
        $summaries = collect($patients['summaries']->toArray()['data']);

        $patientId    = $patient->id;
        $billedForCcm = false;
        $billedForPcm = false;
        $summaries->each(function (ApprovableBillablePatient $item) use ($patientId, &$billedForCcm, &$billedForPcm) {
            $arr = $item->toArray(null);
            if ($arr['id'] !== $patientId) {
                return;
            }

            $arr['chargeable_services']->collection->each(function ($service) use (&$billedForCcm, &$billedForPcm) {
                if (ChargeableService::CCM === $service->code) {
                    $billedForCcm = true;
                }
                if (ChargeableService::PCM === $service->code) {
                    $billedForPcm = true;
                }
            });
        });

        $this->assertEquals(false, $billedForCcm);
        $this->assertEquals(true, $billedForPcm);
    }

    public function test_pcm_not_charged_when_ccm_eligible()
    {
        $practice = $this->setupPractice(true, false, false, true);
        $nurse    = $this->getNurse($practice->id);
        $patient  = $this->setupPatient($practice);

        $this->addTime($nurse, $patient, 35, true, 1);

        $patients  = $this->service->getBillablePatientsForMonth($practice->id, now());
        $summaries = collect($patients['summaries']->toArray()['data']);

        $patientId    = $patient->id;
        $billedForCcm = false;
        $billedForPcm = false;
        $summaries->each(function (ApprovableBillablePatient $item) use ($patientId, &$billedForCcm, &$billedForPcm) {
            $arr = $item->toArray(null);
            if ($arr['id'] !== $patientId) {
                return;
            }

            $arr['chargeable_services']->collection->each(function ($service) use (&$billedForCcm, &$billedForPcm) {
                if (ChargeableService::CCM === $service->code) {
                    $billedForCcm = true;
                }
                if (ChargeableService::PCM === $service->code) {
                    $billedForPcm = true;
                }
            });
        });

        $this->assertEquals(true, $billedForCcm);
        $this->assertEquals(false, $billedForPcm);
    }

    public function test_pcm_not_charged_when_ccm_time_less_than_30()
    {
        $practice = $this->setupPractice(false, false, false, true);
        $nurse    = $this->getNurse($practice->id);
        $patient  = $this->setupPatient($practice, false, true);

        $this->addTime($nurse, $patient, 28, true, 1);

        $patients  = $this->service->getBillablePatientsForMonth($practice->id, now());
        $summaries = collect($patients['summaries']->toArray()['data']);

        $patientId    = $patient->id;
        $billedForCcm = false;
        $billedForPcm = false;
        $summaries->each(function (ApprovableBillablePatient $item) use ($patientId, &$billedForCcm, &$billedForPcm) {
            $arr = $item->toArray(null);
            if ($arr['id'] !== $patientId) {
                return;
            }

            $arr['chargeable_services']->collection->each(function ($service) use (&$billedForCcm, &$billedForPcm) {
                if (ChargeableService::CCM === $service->code) {
                    $billedForCcm = true;
                }
                if (ChargeableService::PCM === $service->code) {
                    $billedForPcm = true;
                }
            });
        });

        $this->assertEquals(false, $billedForCcm);
        $this->assertEquals(false, $billedForPcm);
    }

    public function test_pcm_not_charged_when_not_enabled_for_practice()
    {
        $practice = $this->setupPractice(true);
        $nurse    = $this->getNurse($practice->id);
        $patient  = $this->setupPatient($practice);

        $this->addTime($nurse, $patient, 35, true, 1);

        $patients  = $this->service->getBillablePatientsForMonth($practice->id, now());
        $summaries = collect($patients['summaries']->toArray()['data']);

        $patientId    = $patient->id;
        $billedForCcm = false;
        $billedForPcm = false;
        $summaries->each(function (ApprovableBillablePatient $item) use ($patientId, &$billedForCcm, &$billedForPcm) {
            $arr = $item->toArray(null);
            if ($arr['id'] !== $patientId) {
                return;
            }

            $arr['chargeable_services']->collection->each(function ($service) use (&$billedForCcm, &$billedForPcm) {
                if (ChargeableService::CCM === $service->code) {
                    $billedForCcm = true;
                }
                if (ChargeableService::PCM === $service->code) {
                    $billedForPcm = true;
                }
            });
        });

        $this->assertEquals(true, $billedForCcm);
        $this->assertEquals(false, $billedForPcm);
    }

    private function getNurse($practiceId)
    {
        $nurse = $this->createUser($practiceId, 'care-center');

        $variableRate  = true;
        $enableCcmPlus = true;
        $visitFee      = 12.50;
        $hourlyRate    = 29.0;

        return $this->setupNurse($nurse, $variableRate, $hourlyRate, $enableCcmPlus, $visitFee);
    }
}
