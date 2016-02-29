<?php

use App\CLH\CCD\Importer\CPMProblem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CPMProblemsSeeder extends Seeder
{
    public function run()
    {
//        CPMProblem::updateOrCreate([
//            'name' => '',
//            'icd10from' => '',
//            'icd10to' => '',
//            'icd9from' => '',
//            'icd9to' => '',
//            'contains' => ''
//        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Diabetes',
            'icd10from' => 'E08',
            'icd10to' => 'E13',
            'icd9from' => '250.00',
            'icd9to' => '259.93',
            'contains' => 'diabetes, diabetes mellitus'
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Hypertension',
            'icd10from' => 'I10',
            'icd10to' => 'I13.11',
            'icd9from' => '401.00',
            'icd9to' => '405.00',
            'contains' => 'hypertension'
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Afib',
            'icd10from' => 'I48.0',
            'icd10to' => 'I48.99',
            'icd9from' => '427.00',
            'icd9to' => '427.89',
            'contains' => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction'
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'CAD',
            'icd10from' => 'I25.1',
            'icd10to' => 'I25.9',
            'icd9from' => '414.00',
            'icd9to' => '414.90',
            'contains' => ''
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Depression',
            'icd10from' => 'F32.3',
            'icd10to' => 'F32.9',
            'icd9from' => '296.00',
            'icd9to' => '296.99',
            'contains' => ''
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'CHF',
            'icd10from' => 'I50.1',
            'icd10to' => 'I50.9',
            'icd9from' => '428.00',
            'icd9to' => '428.90',
            'contains' => 'Heart failure'
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'High Cholesterol',
            'icd10from' => 'E78.0',
            'icd10to' => 'E78.9',
            'icd9from' => '272.00',
            'icd9to' => '272.40',
            'contains' => 'Hyperlipidemia'
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Kidney Disease',
            'icd10from' => 'N18.0',
            'icd10to' => 'N18.9',
            'icd9from' => '585.10',
            'icd9to' => '585.90',
            'contains' => ''
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Dementia',
            'icd10from' => 'F01',
            'icd10to' => 'F09',
            'icd9from' => '290.00',
            'icd9to' => '294.21',
            'contains' => ''
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Alzheimers Dementia'
        ], [
            'icd10from' => 'G30.9',
            'icd10to' => 'G30.9',
            'icd9from' => '331.00',
            'icd9to' => '331.82',
            'contains' => ''
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Asthma--COPD',
            'icd10from' => 'J44.9',
            'icd10to' => 'J45.99',
            'icd9from' => '490.00',
            'icd9to' => '496.00',
            'contains' => 'COPD, Chronic obstructive pulmonary disease, Asthma'
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Chronic Pain',
            'icd10from' => 'G89.4',
            'icd10to' => 'G89.4',
            'icd9from' => '338.29',
            'icd9to' => '338.40',
            'contains' => 'Chronic Pain'
        ]);

        CPMProblem::updateOrCreate([
            'name' => 'Obesity',
            'icd10from' => 'E66',
            'icd10to' => 'E66.9',
            'icd9from' => '278.00',
            'icd9to' => '278.01',
            'contains' => 'Obesity'
        ]);

        $this->command->info('All good!');

    }
}