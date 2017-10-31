<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 14/11/2016
 * Time: 4:58 PM
 */

namespace Tests\Helpers;

use App\Models\CCD\CcdVendor;
use App\Models\MedicalRecords\ImportedMedicalRecord;

trait CcdImporterHelpers
{
    /**
     * QA Import a CCDA. This means import all Problems, Meds and so on, without actually creating a User for the
     * patient. Note: An example of $pathToCcda is storage_path('/ccdas/Samples/aprima-sample.xml')
     *
     * @param $pathToCcda
     * @param CcdVendor $ccdVendor
     */
    public function qaImport(
        $pathToCcda,
        CcdVendor $ccdVendor
    ) {
        $this->userLogin($this->admin);

        $this->visit(route('patients.dashboard'))
            ->click('Import CCDs')
            ->seePageIs('/ccd-importer/create')
            ->findElement("label-vendor-{$ccdVendor->id}")
            ->click();

        $this
            ->type($pathToCcda, 'ccd')
            ->press('Upload')
            ->seePageIs('/ccd-importer/qaimport')
            ->see('Name')
            ->see('Provider')
            ->see('Has Phone')
            ->see('Import')
            ->see('Delete')
            ->see('IMPORT/DELETE CHECKED CCDS');

        $summary = ImportedMedicalRecord::all()->last();

        $this->assertTrue(!empty($summary->name));
        $this->see($summary->name);

        $this->assertGreaterThan(0, $summary->medications);
        $this->assertGreaterThan(0, $summary->problems);

        $this->assertTrue(boolval($summary->has_phone));

        return $this;
    }
}
