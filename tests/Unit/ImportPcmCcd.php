<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Constants;
use App\Models\CCD\CcdVendor;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

class ImportPcmCcd extends TestCase
{
    use UserHelpers;
    use PracticeHelpers;

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Import ccd with only one problem from practice that has PCM enabled.
     *
     * @return void
     * @throws \Exception
     */
    public function test_import_pcm_ccd()
    {
        //TODO
        //getting this exception
        //Illuminate\Contracts\Container\BindingResolutionException : Unresolvable dependency resolving [Parameter #0 [ <required> $model ]] in class Algolia\ScoutExtended\Builder
        //->had to disable Laravel Profile (PROFILER_ENABLED=false in phpunit.xml)

//        $practice = $this->getPractice(true, false, false, true);
//
//        /** @var CpmProblem $problem */
//        $problem = CpmProblem::whereName('Asthma')->first();
//
//        PcmProblem::firstOrCreate([
//            'name' => $problem->name,
//        ], [
//            'code_type'   => Constants::ICD10,
//            'code'        => $problem->default_icd_10_code,
//            'description' => $problem->name,
//            'practice_id' => $practice->id,
//        ]);
//
//        $problem->delete();
//
//        $xmlName = 'demo.xml';
//        $xmlPath = storage_path('ccdas/Samples/demo.xml');
//
//        $patient = $this->importPcmPatientFromXml($xmlName, $xmlPath, $practice->id);
//
//        // should have one problem only
//        // should be PCM even if practice has ccm
//        $this->assertTrue($patient->isPcm());
//        $this->assertFalse($patient->isCcm());
    }

    /**
     * Import ccd with 2 problems from practice that has both CCM and PCM enabled.
     *
     * @return void
     */
    public function test_import_ccd_patient_billed_for_ccm_because_more_than_one_problem()
    {
        //TODO
//        $xmlName  = 'demo.xml';
//        $xmlPath  = storage_path('ccdas/Samples/demo.xml');
//        $practice = $this->getPractice(true, false, false, true);
//        $patient  = $this->importPcmPatientFromXml($xmlName, $xmlPath, $practice->id);
//
//        $this->assertFalse($patient->isPcm());
//        $this->assertTrue($patient->isCcm());
    }

    /**
     * Import ccd with 2 problems from practice that has only PCM enabled.
     *
     * @return void
     */
    public function test_import_ccd_patient_billed_for_pcm()
    {
        //TODO
//        $xmlName  = 'demo.xml';
//        $xmlPath  = storage_path('ccdas/Samples/demo.xml');
//        $practice = $this->getPractice(false, false, false, true);
//        $patient  = $this->importPcmPatientFromXml($xmlName, $xmlPath, $practice->id);
//
//        $this->assertTrue($patient->isPcm());
//        $this->assertFalse($patient->isCcm());
    }

    private function importPcmPatientFromXml($xmlName, $xmlPath, $practiceId): User
    {
        Config::set('ccda-parser.store_results_in_db', false);

        $admin = $this->createUser($practiceId, 'administrator');
        $this->be($admin);

        User::where('display_name', '=', 'Myra Jones')->delete();

        $uploadCcdResponse = $this->json('POST', 'api/ccd-importer/imported-medical-records?json', [
            'file' => [new UploadedFile($xmlPath, $xmlName, 'text/xml', null, true)],
        ]);

        self::assertEquals(200, $uploadCcdResponse->status());

        $result = $uploadCcdResponse->json();
        self::assertNotEmpty($result);

        $ccdaId = $result['ccdas'][0];

        /** @var ImportedMedicalRecord $imr */
        $imr = ImportedMedicalRecord::where('medical_record_id', '=', $ccdaId)->first();
        $this->assertNotNull($imr);;

        $confirmCcdResponse = $this->json('POST', 'api/ccd-importer/records/confirm', [
            [
                'id'               => $imr->id,
                'Location'         => $imr->location_id ?? null,
                'Practice'         => $practiceId,
                'Billing Provider' => $imr->billing_provider_id ?? null,
            ],
        ]);
        self::assertTrue(200 === $confirmCcdResponse->status());
        self::assertNotEmpty($confirmCcdResponse->json());
        self::assertArrayHasKey('patient', $confirmCcdResponse->json()[0]);
        $patientId = $confirmCcdResponse->json()[0]['patient']['id'];
        $patient   = User::with('patientInfo')->find($patientId);
        self::assertNotNull($patient);

        return $patient;
    }

    private function getPractice(
        bool $addCcmService = false,
        bool $addCcmPlusServices = false,
        bool $addBhiService = false,
        bool $addPcmService = false
    ): Practice {
        /** @var CcdVendor $ccdVendor */
        $ccdVendor = CcdVendor::first();
        if ( ! $ccdVendor) {
            $ccdVendor = factory(CcdVendor::class)->create();
        }

        $practice = Practice::find($ccdVendor->program_id);
        $practice = $this->setupExistingPractice($practice, true, false, true, true);

        return $practice;
    }
}
