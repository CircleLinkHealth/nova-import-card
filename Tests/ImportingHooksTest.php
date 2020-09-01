<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Eligibility\CcdaImporter\FiresImportingHooks;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\GetUPG0506ProblemInstruction;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportProblems;
use CircleLinkHealth\Eligibility\Tests\Fakers\FakeCalvaryCcda;
use Tests\CustomerTestCase;

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

        $ccda     = FakeCalvaryCcda::create();
        $importer = new ImportPatientInfo($this->patient(), $ccda);
        $this->assertTrue(FiresImportingHooks::shouldRunHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, $this->practice()));

        $oldValue = $this->patient()->patientInfo;
        $hook     = FiresImportingHooks::fireImportingHook(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, $this->patient(), $ccda, $this->patient()->patientInfo);
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
        $this->practice()->storeImportingHook(ImportProblems::IMPORTING_PROBLEM_INSTRUCTIONS, GetUPG0506ProblemInstruction::IMPORTING_LISTENER_NAME);
        $this->assertJson($this->practice()->importing_hooks);

        $ccda     = FakeCalvaryCcda::create();
        $importer = new ImportProblems($this->patient(), $ccda);
        $this->assertTrue(FiresImportingHooks::shouldRunHook(ImportProblems::IMPORTING_PROBLEM_INSTRUCTIONS, $this->practice()));

        $oldValue = $this->patient()->patientInfo;
        $hook     = FiresImportingHooks::fireImportingHook(ImportProblems::IMPORTING_PROBLEM_INSTRUCTIONS, $this->patient(), $ccda, $this->patient()->patientInfo);
        $this->assertNull($hook);
    }
}
