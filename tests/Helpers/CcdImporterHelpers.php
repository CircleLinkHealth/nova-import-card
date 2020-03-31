<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers;

use App\Models\CCD\CcdVendor;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;

trait CcdImporterHelpers
{
    /**
     * QA Import a CCDA. This means import all Problems, Meds and so on, without actually creating a User for the
     * patient. Note: An example of $pathToCcda is storage_path('/ccdas/Samples/aprima-sample.xml').
     *
     * @param $pathToCcda
     * @param CcdVendor $ccdVendor
     */
    public function qaImport(
        $pathToCcda,
        CcdVendor $ccdVendor
    ) {
        $this->userLogin($this->admin);

        $response = $this->get(route('patients.dashboard'))
            ->click('Import CCDs')
            ->seePageIs('/ccd-importer/create')
            ->findElement("label-vendor-{$ccdVendor->id}")
            ->click();

        $this
            ->type($pathToCcda, 'ccd')
            ->press('Upload')
            ->seePageIs('/ccd-importer/qaimport')
            ->assertSee('Name')
            ->assertSee('Provider')
            ->assertSee('Has Phone')
            ->assertSee('Import')
            ->assertSee('Delete')
            ->assertSee('IMPORT/DELETE CHECKED CCDS');

        $summary = ImportedMedicalRecord::all()->last();

        $this->assertTrue( ! empty($summary->name));
        $response->assertSee(sanitizeString($summary->name));

        $this->assertGreaterThan(0, $summary->medications);
        $this->assertGreaterThan(0, $summary->problems);

        $this->assertTrue(boolval($summary->has_phone));

        return $this;
    }
}
