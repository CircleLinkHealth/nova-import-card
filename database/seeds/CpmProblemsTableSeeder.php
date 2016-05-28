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
                'care_item_id' => 28,
                'name' => 'Diabetes',
                'icd10from' => 'E08',
                'icd10to' => 'E13.0',
                'icd9from' => 250,
                'icd9to' => 259.93000000000001,
                'contains' => 'diabetes, diabetes mellitus',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:28',
                'care_item_name' => 'diabetes',
            ),
            1 => 
            array (
                'id' => 2,
                'care_item_id' => 24,
                'name' => 'Hypertension',
                'icd10from' => 'I10',
                'icd10to' => 'I13.11',
                'icd9from' => 401,
                'icd9to' => 405,
                'contains' => 'hypertension',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:28',
                'care_item_name' => 'hypertension',
            ),
            2 => 
            array (
                'id' => 3,
                'care_item_id' => 31,
                'name' => 'Afib',
                'icd10from' => 'I48',
                'icd10to' => 'I48.99',
                'icd9from' => 427,
                'icd9to' => 427.88999999999999,
                'contains' => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:28',
                'care_item_name' => 'afib',
            ),
            3 => 
            array (
                'id' => 4,
                'care_item_id' => 30,
                'name' => 'CAD',
                'icd10from' => 'I25',
                'icd10to' => 'I25.9',
                'icd9from' => 414,
                'icd9to' => 414.89999999999998,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:28',
                'care_item_name' => 'cad',
            ),
            4 => 
            array (
                'id' => 5,
                'care_item_id' => 29,
                'name' => 'Depression',
                'icd10from' => 'F32.3',
                'icd10to' => 'F32.9',
                'icd9from' => 296,
                'icd9to' => 296.99000000000001,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:29',
                'care_item_name' => 'depression',
            ),
            5 => 
            array (
                'id' => 6,
                'care_item_id' => 25,
                'name' => 'CHF',
                'icd10from' => 'I50.1',
                'icd10to' => 'I50.9',
                'icd9from' => 428,
                'icd9to' => 428.89999999999998,
                'contains' => 'Heart failure',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:29',
                'care_item_name' => 'chf',
            ),
            6 => 
            array (
                'id' => 7,
                'care_item_id' => 27,
                'name' => 'High Cholesterol',
                'icd10from' => 'E78.0',
                'icd10to' => 'E78.9',
                'icd9from' => 272,
                'icd9to' => 272.39999999999998,
                'contains' => 'Hyperlipidemia',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:29',
                'care_item_name' => 'high-cholesterol',
            ),
            7 => 
            array (
                'id' => 8,
                'care_item_id' => 435,
                'name' => 'Kidney Disease',
                'icd10from' => 'N18.0',
                'icd10to' => 'N18.9',
                'icd9from' => 585.10000000000002,
                'icd9to' => 585.89999999999998,
                'contains' => 'Chronic Kidney Disease, Kidney Disease',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:29',
                'care_item_name' => 'kidney-disease',
            ),
            8 => 
            array (
                'id' => 9,
                'care_item_id' => 434,
                'name' => 'Dementia',
                'icd10from' => 'F01',
                'icd10to' => 'F09',
                'icd9from' => 290,
                'icd9to' => 294.20999999999998,
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:29',
                'care_item_name' => 'dementia',
            ),
            9 => 
            array (
                'id' => 11,
                'care_item_id' => 26,
                'name' => 'Asthma--COPD',
                'icd10from' => 'J44.9',
                'icd10to' => 'J45.99',
                'icd9from' => 490,
                'icd9to' => 496,
                'contains' => 'COPD, Chronic obstructive pulmonary disease, Asthma',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-04-25 18:37:29',
                'care_item_name' => 'asthmacopd',
            ),
            10 => 
            array (
                'id' => 14,
                'care_item_id' => 56,
                'name' => 'Smoking',
                'icd10from' => 'F17',
                'icd10to' => 'F17.3',
                'icd9from' => 305.10000000000002,
                'icd9to' => 305.10000000000002,
                'contains' => '',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '2016-05-17 11:35:29',
                'care_item_name' => 'cf-sol-smo-10-smoking',
            ),
        ));
        
        
    }
}
