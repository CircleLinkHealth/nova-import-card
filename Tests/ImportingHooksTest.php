<?php

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;
use CircleLinkHealth\Eligibility\Tests\Fakers\FakeCalvaryCcda;
use Tests\CustomerTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportingHooksTest extends CustomerTestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_it_stores_importing_hook()
    {
        $this->assertNull($this->practice()->importing_hooks);
        $this->practice()->storeImportingHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME);
        $this->assertJson($this->practice()->importing_hooks);
     
        $ccda = FakeCalvaryCcda::create();
        $importer = new ImportPatientInfo($this->patient(), $ccda);
        $this->assertTrue($importer->shouldRunHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, $this->practice()));
        
        $oldValue = $this->patient()->patientInfo;
        $hook = $importer->fireImportingHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, $this->patient(), $ccda, $this->patient()->patientInfo);
        $this->assertNull($hook);
    }
}
