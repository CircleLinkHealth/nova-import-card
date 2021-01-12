<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\CustomerTestCase;

class ImportCcdFromAwv extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setAdminUser();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_import_from_source_awv_and_create_patient_with_is_awv()
    {
        //getting this exception
        //Illuminate\Contracts\Container\BindingResolutionException : Unresolvable dependency resolving [Parameter #0 [ <required> $model ]] in class Algolia\ScoutExtended\Builder
        //->had to disable Laravel Profile (PROFILER_ENABLED=false in phpunit.xml)

        User::where('display_name', '=', 'Myra Jones')->delete();
        $ccdaPath          = storage_path('ccdas/Samples/demo.xml');
        $uploadCcdResponse = $this->json('POST', 'api/ccd-importer/imported-medical-records?json&source=importer_awv', [
            'file' => [new UploadedFile($ccdaPath, 'demo.xml', 'text/xml', null, true)],
        ]);

        self::assertEquals(200, $uploadCcdResponse->status());
        self::assertNotEmpty($uploadCcdResponse->json());
        self::assertArrayHasKey('medical_record_id', $uploadCcdResponse->json()[0]);

        $confirmCcdResponse = $this->json('POST', 'api/ccd-importer/records/confirm', [
            [
                'id'               => $uploadCcdResponse->json()[0]['medical_record_id'],
                'Location'         => $uploadCcdResponse->json()[0]['location_id'] ?? null,
                'Practice'         => $this->practice()->id,
                'Billing Provider' => $uploadCcdResponse->json()[0]['billing_provider_id'] ?? null,
            ],
        ]);
        self::assertTrue(200 === $confirmCcdResponse->status());
        self::assertNotEmpty($confirmCcdResponse->json());
        self::assertArrayHasKey('patient', $confirmCcdResponse->json()[0]);
        $patientId = $confirmCcdResponse->json()[0]['patient']['id'];
        $patient   = User::with('patientInfo')->find($patientId);
        self::assertNotNull($patient);
        self::assertTrue(1 === $patient->patientInfo->is_awv);
    }

    /**
     * Creates an admin user to be used with tests.
     *
     * @return User
     */
    private function createAdminUser()
    {
        /** @var User $user */
        $user      = factory(User::class)->create();
        $adminRole = Role::getIdsFromNames(['administrator']);
        $user->attachGlobalRole($adminRole);

        return $user;
    }

    /**
     * Become an admin user for the session.
     */
    private function setAdminUser()
    {
        $admin = $this->createAdminUser();
        $this->be($admin);
    }
}
