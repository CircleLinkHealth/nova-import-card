<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\ConsolidatesMedicationInfo;
use Tests\TestCase;

class ConsolidateMedicationInfoTest extends TestCase
{
    use ConsolidatesMedicationInfo;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_it_chooses_the_correct_medication_name()
    {
        $log = (object) [
            'reference'                    => null,
            'reference_title'              => null,
            'reference_sig'                => null,
            'text'                         => null,
            'product_name'                 => 'metformin ER 500 mg tablet,extended release 24 hr',
            'product_code'                 => '860975',
            'product_code_system'          => '2.16.840.1.113883.6.88',
            'product_text'                 => null,
            'translation_name'             => null,
            'translation_code'             => null,
            'translation_code_system'      => null,
            'translation_code_system_name' => null,
            'import'                       => true,
        ];

        $consMed = $this->consolidateMedicationInfo($log);

        $this->assertEquals($log->product_name, $consMed->cons_name);
        $this->assertFalse($this->containsSigKeywords($consMed->cons_name));
    }
}
