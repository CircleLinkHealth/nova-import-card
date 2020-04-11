<?php

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\GetUPG0506ProblemInstruction;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportProblems;
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
    public function test_it_patient_info_importing_hook()
    {
        $this->assertNull($this->practice()->importing_hooks);
        $this->practice()->storeImportingHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME);
        $this->assertJson($this->practice()->importing_hooks);
     
        $ccda = FakeCalvaryCcda::create();
        $importer = new ImportPatientInfo($this->patient(), $ccda);
        $this->assertTrue($importer->shouldRunHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, $this->practice()));
        
        $oldValue = $this->patient()->patientInfo;
        $hook = $importer->fireImportingHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, $this->patient(), $ccda, $this->patient()->patientInfo);
        $this->assertEquals($oldValue, $hook);
    }
    
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_it_upg0506_instructions_importing_hook()
    {
        $this->assertNull($this->practice()->importing_hooks);
        $this->practice()->storeImportingHook(ImportProblems::HOOK_USE_DIFFERENT_INSTRUCTION_IMPORTER, GetUPG0506ProblemInstruction::IMPORTING_LISTENER_NAME);
        $this->assertJson($this->practice()->importing_hooks);
        
        $ccda = FakeCalvaryCcda::create();
        $importer = new ImportProblems($this->patient(), $ccda);
        $this->assertTrue($importer->shouldRunHook(ImportProblems::HOOK_USE_DIFFERENT_INSTRUCTION_IMPORTER, $this->practice()));
        
        $oldValue = $this->patient()->patientInfo;
        $hook = $importer->fireImportingHook(ImportProblems::HOOK_USE_DIFFERENT_INSTRUCTION_IMPORTER, $this->patient(), $ccda, $this->patient()->patientInfo);
        $this->assertNull($hook);
    }
}
