<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\CpmConstants;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\CustomerTestCase;

class ImportPcmCcd extends CustomerTestCase
{
    use PracticeHelpers;
    use UserHelpers;

    /**
     * Import ccd with only one problem from practice that has PCM enabled.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function test_import_pcm_ccd()
    {
        User::where('display_name', '=', 'Myra Jones')->delete();

        $practice = $this->getPractice(true, false, false, true);

        $problems = ['Asthma', 'Pneumonia'];
        foreach ($problems as $problemName) {
            /** @var CpmProblem $problem */
            $problem = CpmProblem::whereName($problemName)->first();

            if ( ! $problem) {
                continue;
            }

            PcmProblem::firstOrCreate([
                'description' => $problem->name,
            ], [
                'code_type'   => CpmConstants::ICD10_NAME,
                'code'        => $problem->default_icd_10_code,
                'description' => $problem->name,
                'practice_id' => $practice->id,
            ]);
        }

        $xmlName = 'demo.xml';
        $xmlPath = storage_path('ccdas/Samples/demo.xml');

        $patient = $this->importPcmPatientFromXml($xmlName, $xmlPath, $practice->id);

        // should have one problem only
        // should be PCM even if practice has ccm
        $this->assertTrue($patient->isPcm());
    }

    private function getPractice(
        bool $addCcmService = false,
        bool $addCcmPlusServices = false,
        bool $addBhiService = false,
        bool $addPcmService = false
    ): Practice {
        $practice = Practice::find($this->practice()->id);

        return $this->setupExistingPractice($practice, true, false, true, true);
    }

    private function importPcmPatientFromXml($xmlName, $xmlPath, $practiceId): User
    {
        Config::set('ccda-parser.store_results_in_db', false);

        $admin = $this->createUser($practiceId, 'administrator');
        $this->be($admin);

        $uploadCcdResponse = $this->json('POST', 'api/ccd-importer/imported-medical-records?json', [
            'file' => [new UploadedFile($xmlPath, $xmlName, 'text/xml', null, true)],
        ]);

        self::assertTrue(200 === $uploadCcdResponse->status());

        $result = $uploadCcdResponse->json();
        self::assertNotEmpty($result);

        $ccda = Ccda::findOrFail($result['ccdas'][0]);

        $confirmCcdResponse = $this->json('POST', 'api/ccd-importer/records/confirm', [
            [
                'id'               => $ccda->id,
                'Location'         => $ccda->location_id ?? null,
                'Practice'         => $practiceId,
                'Billing Provider' => $ccda->billing_provider_id ?? null,
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
}
