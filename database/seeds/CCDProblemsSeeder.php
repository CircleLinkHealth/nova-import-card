<?php

use App\CLH\CCD\Importer\CCDProblem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CCDProblemsSeeder extends Seeder
{
    public function run()
    {
//        CCDProblem::updateOrCreate([
//            'name' => ''
//        ], [
//            'icd10from' => '',
//            'icd10to' => '',
//            'icd9from' => '',
//            'icd9to' => '',
//            'contains' => ''
//        ]);

        CCDProblem::updateOrCreate([
            'name' => 'diabetes'
        ], [
            'icd10from' => 'E11.0',
            'icd10to' => 'E11.9',
            'icd9from' => '250.00',
            'icd9to' => '259.93',
            'contains' => 'diabetes, diabetes mellitus'
        ]);

        CCDProblem::updateOrCreate([
            'name' => 'Diabetes'
        ], [
            'icd10from' => 'E08.0',
            'icd10to' => 'E13.0',
            'icd9from' => '250.00',
            'icd9to' => '259.93',
            'contains' => ''
        ]);

        CCDProblem::updateOrCreate([
            'name' => 'Hypertension'
        ], [
            'icd10from' => 'I10.0',
            'icd10to' => 'I13.11',
            'icd9from' => '401.00',
            'icd9to' => '405.00',
            'contains' => 'hypertension'
        ]);

        CCDProblem::updateOrCreate([
            'name' => 'COPD/Asthma'
        ], [
            'icd10from' => 'J44.9',
            'icd10to' => 'J45.99',
            'icd9from' => '490.00',
            'icd9to' => '496.00',
            'contains' => 'COPD, Chronic obstructive pulmonary disease'
        ]);

        CCDProblem::updateOrCreate([
            'name' => 'Afib'
        ], [
            'icd10from' => 'I48.0',
            'icd10to' => 'I48.99',
            'icd9from' => '427.00',
            'icd9to' => '427.89',
            'contains' => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction'
        ]);
    }
}