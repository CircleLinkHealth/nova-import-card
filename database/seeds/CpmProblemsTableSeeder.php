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
                'id' => '1',
                'name' => 'Diabetes',
                'icd10from' => 'E08',
                'icd10to' => 'E13.0',
                'icd9from' => '250.00',
                'icd9to' => '259.93',
                'contains' => 'diabetes, diabetes mellitus',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'diabetes',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Hypertension',
                'icd10from' => 'I10',
                'icd10to' => 'I13.11',
                'icd9from' => '401.00',
                'icd9to' => '405.00',
                'contains' => 'hypertension',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'hypertension',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Afib',
                'icd10from' => 'I48',
                'icd10to' => 'I48.99',
                'icd9from' => '427.00',
                'icd9to' => '427.89',
                'contains' => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'afib',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'CAD',
                'icd10from' => 'I25',
                'icd10to' => 'I25.9',
                'icd9from' => '414.00',
                'icd9to' => '414.90',
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'cad',
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'Depression',
                'icd10from' => 'F32.3',
                'icd10to' => 'F32.9',
                'icd9from' => '296.00',
                'icd9to' => '296.99',
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'depression',
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'CHF',
                'icd10from' => 'I50.1',
                'icd10to' => 'I50.9',
                'icd9from' => '428.00',
                'icd9to' => '428.90',
                'contains' => 'Heart failure',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'chf',
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'High Cholesterol',
                'icd10from' => 'E78.0',
                'icd10to' => 'E78.9',
                'icd9from' => '272.00',
                'icd9to' => '272.40',
                'contains' => 'Hyperlipidemia',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'high-cholesterol',
            ),
            7 => 
            array (
                'id' => '8',
                'name' => 'Kidney Disease',
                'icd10from' => 'N18.0',
                'icd10to' => 'N18.9',
                'icd9from' => '585.10',
                'icd9to' => '585.90',
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'kidney-disease',
            ),
            8 => 
            array (
                'id' => '9',
                'name' => 'Dementia',
                'icd10from' => 'F01',
                'icd10to' => 'F09',
                'icd9from' => '290.00',
                'icd9to' => '294.21',
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'dementia',
            ),
            9 => 
            array (
                'id' => '10',
                'name' => 'Alzheimers Dementia',
                'icd10from' => 'G30.9',
                'icd10to' => 'G30.9',
                'icd9from' => '331.00',
                'icd9to' => '331.82',
                'contains' => '',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-02-15 16:14:38',
                'care_item_name' => 'alzheimers-dementia',
            ),
            10 => 
            array (
                'id' => '11',
                'name' => 'Asthma--COPD',
                'icd10from' => 'J44.9',
                'icd10to' => 'J45.99',
                'icd9from' => '490.00',
                'icd9to' => '496.00',
                'contains' => 'COPD, Chronic obstructive pulmonary disease, Asthma',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'copd',
            ),
            11 => 
            array (
                'id' => '12',
                'name' => 'Chronic Pain',
                'icd10from' => 'G89.4',
                'icd10to' => 'G89.4',
                'icd9from' => '338.29',
                'icd9to' => '338.40',
                'contains' => 'Chronic Pain',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'chronic-pain',
            ),
            12 => 
            array (
                'id' => '13',
                'name' => 'Obesity',
                'icd10from' => 'E66',
                'icd10to' => 'E66.9',
                'icd9from' => '278.00',
                'icd9to' => '278.01',
                'contains' => 'Obesity',
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2016-01-27 13:11:24',
                'care_item_name' => 'obesity',
            ),
            13 => 
            array (
                'id' => '14',
                'name' => 'Diabetes',
                'icd10from' => 'E08',
                'icd10to' => 'E13',
                'icd9from' => '250.00',
                'icd9to' => '259.93',
                'contains' => 'diabetes, diabetes mellitus',
                'created_at' => '2016-02-15 16:14:38',
                'updated_at' => '2016-02-15 16:14:38',
                'care_item_name' => 'diabetes',
            ),
            14 => 
            array (
                'id' => '15',
                'name' => 'Afib',
                'icd10from' => 'I48.0',
                'icd10to' => 'I48.99',
                'icd9from' => '427.00',
                'icd9to' => '427.89',
                'contains' => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction',
                'created_at' => '2016-02-15 16:14:38',
                'updated_at' => '2016-02-15 16:14:38',
                'care_item_name' => 'afib',
            ),
            15 => 
            array (
                'id' => '16',
                'name' => 'CAD',
                'icd10from' => 'I25.1',
                'icd10to' => 'I25.9',
                'icd9from' => '414.00',
                'icd9to' => '414.90',
                'contains' => '',
                'created_at' => '2016-02-15 16:14:38',
                'updated_at' => '2016-02-15 16:14:38',
                'care_item_name' => 'cad',
            ),
        ));
        
        
    }
}
