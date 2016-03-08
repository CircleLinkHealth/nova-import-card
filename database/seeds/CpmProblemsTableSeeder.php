<?php

use Illuminate\Database\Seeder;

class CpmProblemsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('cpm_problems')->delete();
        
        \DB::table('cpm_problems')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Diabetes',
                'icd10from' => 'E08',
                'icd10to' => 'E13.0',
                'icd9from' => 250,
                'icd9to' => 259.93000000000001,
                'contains' => 'diabetes, diabetes mellitus',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Hypertension',
                'icd10from' => 'I10',
                'icd10to' => 'I13.11',
                'icd9from' => 401,
                'icd9to' => 405,
                'contains' => 'hypertension',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Afib',
                'icd10from' => 'I48',
                'icd10to' => 'I48.99',
                'icd9from' => 427,
                'icd9to' => 427.88999999999999,
                'contains' => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'CAD',
                'icd10from' => 'I25',
                'icd10to' => 'I25.9',
                'icd9from' => 414,
                'icd9to' => 414.89999999999998,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Depression',
                'icd10from' => 'F32.3',
                'icd10to' => 'F32.9',
                'icd9from' => 296,
                'icd9to' => 296.99000000000001,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'CHF',
                'icd10from' => 'I50.1',
                'icd10to' => 'I50.9',
                'icd9from' => 428,
                'icd9to' => 428.89999999999998,
                'contains' => 'Heart failure',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'High Cholesterol',
                'icd10from' => 'E78',
                'icd10to' => 'E78.9',
                'icd9from' => 272,
                'icd9to' => 272.39999999999998,
                'contains' => 'Hyperlipidemia',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'Kidney Disease',
                'icd10from' => 'N18',
                'icd10to' => 'N18.9',
                'icd9from' => 585.10000000000002,
                'icd9to' => 585.89999999999998,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'Dementia',
                'icd10from' => 'F01',
                'icd10to' => 'F09',
                'icd9from' => 290,
                'icd9to' => 294.20999999999998,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'Alzheimers Dementia',
                'icd10from' => 'G30.9',
                'icd10to' => 'G30.9',
                'icd9from' => 331,
                'icd9to' => 331.81999999999999,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-02-15 16:14:38',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'Asthma--COPD',
                'icd10from' => 'J44.9',
                'icd10to' => 'J45.99',
                'icd9from' => 490,
                'icd9to' => 496,
                'contains' => 'COPD, Chronic obstructive pulmonary disease, Asthma',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'Chronic Pain',
                'icd10from' => 'G89.4',
                'icd10to' => 'G89.4',
                'icd9from' => 338.29000000000002,
                'icd9to' => 338.39999999999998,
                'contains' => 'Chronic Pain',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'Obesity',
                'icd10from' => 'E66',
                'icd10to' => 'E66.9',
                'icd9from' => 278,
                'icd9to' => 278.00999999999999,
                'contains' => 'Obesity',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
            ),
        ));
        
        
    }
}
