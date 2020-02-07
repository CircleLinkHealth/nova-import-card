<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use CircleLinkHealth\Eligibility\EligibilityChecker;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\TestCase;

class EligibilityProcessingTest extends TestCase
{
    public function test_it_processes_phx_heart_record()
    {
        $practice = Practice::firstOrFail();
        $batch    = $this
            ->app
            ->make(ProcessEligibilityService::class)
            ->createBatch(
                EligibilityBatch::TYPE_PHX_DB_TABLES,
                $practice->id,
                [
                    'filterLastEncounter' => false,
                    'filterInsurance'     => true,
                    'filterProblems'      => true,
                ]
            );

        $json = '{"id": 12345, "dob": "1980-01-01", "mrn": "123456", "zip": "12345", "city": "NYC", "email": null, "state": "NY", "gender": "F", "street": "123 Summer Street", "phone_1": 2012819204, "phone_2": "", "phone_3": "", "street2": "ABC", "eligible": null, "problems": [{"end": null, "code": "I48.91", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "401.9", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "I50.9", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "585.9", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "786.05", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "396.3", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "401.9", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "434.91", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "585.9", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}, {"end": null, "code": "786.05", "name": null, "start": null, "status": null, "code_system_name": null, "problem_code_system_id": null}], "address_1": "5747 W Missouri Ave", "address_2": "Lot 44", "last_name": "Knoblock", "processed": 0, "cell_phone": "", "created_at": "2018-11-01 13:00:00", "first_name": "Walter", "home_phone": "", "insurances": [{"type": "Test MEdicare"}, {"type": "Test Medicare 2"}], "patient_id": "12345", "phone_1_type": "home", "phone_2_type": null, "phone_3_type": null, "primary_phone": 2012819204, "patient_last_name": "Doe", "patient_first_name": "Janis", "provider_last_name": "Raph MD", "patient_middle_name": "", "provider_first_name": "Dr.", "referring_provider_name": "Dr. Raph MD"}';
        $data = json_decode($json, true);

        $job = EligibilityJob::create([
            'batch_id' => $batch->id,
            'hash'     => 'JaneDoeTestPatient',
            'data'     => $data,
        ]);

        $list = (new EligibilityChecker(
            $job,
            $practice,
            $batch,
            false,
            true,
            true,
            true
        ));

        $this->assertTrue(3 == $list->getEligibilityJob()->status);
    }
}
