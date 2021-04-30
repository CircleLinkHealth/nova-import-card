<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;


use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientProblemsForBillingProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;

class DualCcmAndBhiProblemsTest extends CustomerTestCase
{
    use PracticeHelpers;
    use UserHelpers;

    protected $location;

    protected $practice;

    public function setUp(): void
    {
        parent::setUp();

        $this->practice = $this->setupPractice(true, true, true, true);
    }

    public function test_dual_ccm_bhi_conditions()
    {
        $patient = $this->setupUser(
            $this->practice->id,
            [Role::where('name', 'participant')->firstOrFail()->id],
            'enrolled');

        $dualConditions = CpmProblem::whereIn('name', CpmProblem::DUAL_CCM_BHI_CONDITIONS)
            ->get();

        $toCreate = [];
        foreach ($dualConditions as $dualCondition){
            $toCreate[] = [
                'name' => $dualCondition->name,
                'is_monitored' => 1,
                'cpm_problem_id' => $dualCondition->id
            ];
        }
        $patient->ccdProblems()->createMany($toCreate);
        $patient->load('ccdProblems');

        $problems = PatientProblemsForBillingProcessing::getCollectionFromPatient($patient, Carbon::now()->startOfMonth());

        self::assertEquals($problems->count(), $dualConditions->count());
        foreach ($problems as $problem){
            self::assertTrue(is_a($problem, PatientProblemForProcessing::class));
            self::assertEquals(count($codes = $problem->getServiceCodes()), 2);
            self::assertTrue(in_array(ChargeableService::BHI, $codes));
            self::assertTrue(in_array(ChargeableService::CCM, $codes));
        }
    }
}