<?php

use Illuminate\Database\Seeder;
use App\Models\CPM\CpmProblem;

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
        
        \DB::table('cpm_problems')->insert($this->problems());

        $defaultCarePlan = getDefaultCarePlanTemplate();

        CpmProblem::get()->map(function ($cpmProblem) use ($defaultCarePlan) {
            if ( ! in_array($cpmProblem->id, $defaultCarePlan->cpmProblems->pluck('id')->all())) {
                $defaultCarePlan->cpmProblems()->attach($cpmProblem, [
                    'has_instruction' => true,
                    'page'            => 1
                ]);
            }

            SnomedToCpmIcdMap::updateOrCreate([
                'icd_10_code' => $cpmProblem->default_icd_10_code,
            ], [
                'cpm_problem_id' => $cpmProblem->id,
                'icd_10_name'    => $cpmProblem->name,
            ]);

            $this->command->info("$cpmProblem->name has been added");
        });
    }

    public function problems() : array {
        return array (
            0 => 
            array (
                'id' => 1,
                'default_icd_10_code' => 'E11.8',
                'name' => 'Diabetes',
                'icd10from' => 'E08',
                'icd10to' => 'E13.0',
                'icd9from' => 250.0,
                'icd9to' => 259.93,
                'contains' => 'diabetes, diabetes mellitus, Disorder Due To Type 2 Diabetes Mellitus',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            1 => 
            array (
                'id' => 2,
                'default_icd_10_code' => 'I10',
                'name' => 'Hypertension',
                'icd10from' => 'I10',
                'icd10to' => 'I13.11',
                'icd9from' => 401.0,
                'icd9to' => 405.0,
                'contains' => 'Hypertension, Benign Hypertensive Heart Disease, Benign Hypertension, Hypertensive, Hypertensive disorder, Hyper tension, High Blood Pressure',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            2 => 
            array (
                'id' => 3,
                'default_icd_10_code' => 'I48.91',
                'name' => 'Afib',
                'icd10from' => 'I48',
                'icd10to' => 'I48.99',
                'icd9from' => 427.0,
                'icd9to' => 427.89,
                'contains' => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            3 => 
            array (
                'id' => 4,
                'default_icd_10_code' => 'I25.9',
                'name' => 'CAD/IHD',
                'icd10from' => 'I25',
                'icd10to' => 'I25.9',
                'icd9from' => 414.0,
                'icd9to' => 414.9,
                'contains' => 'cad, Coronary arteriosclerosis',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            4 => 
            array (
                'id' => 5,
                'default_icd_10_code' => 'F33.9',
                'name' => 'Depression',
                'icd10from' => 'F32.0',
                'icd10to' => 'F33.9',
                'icd9from' => 296.0,
                'icd9to' => 296.99,
                'contains' => 'Depression, Depressive Disorder, Severe Recurrent Major Depression Without Psychotic Features, Major Depressive Disorder',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            5 => 
            array (
                'id' => 6,
                'default_icd_10_code' => 'I50.9',
                'name' => 'CHF',
                'icd10from' => 'I50.1',
                'icd10to' => 'I50.9',
                'icd9from' => 428.0,
                'icd9to' => 428.9,
                'contains' => 'Heart failure, congestive heart failure',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            6 => 
            array (
                'id' => 7,
                'default_icd_10_code' => 'E78.5',
                'name' => 'High Cholesterol',
                'icd10from' => 'E78.0',
                'icd10to' => 'E78.9',
                'icd9from' => 272.0,
                'icd9to' => 272.4,
                'contains' => 'Hyperlipidemia, Cholesterol, High Lipids',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            7 => 
            array (
                'id' => 8,
                'default_icd_10_code' => 'N18.9',
                'name' => 'Kidney Disease',
                'icd10from' => 'N18.0',
                'icd10to' => 'N18.9',
                'icd9from' => 585.1,
                'icd9to' => 585.9,
                'contains' => 'Chronic Kidney Disease, Kidney Disease',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            8 => 
            array (
                'id' => 9,
                'default_icd_10_code' => 'F03',
                'name' => 'Dementia',
                'icd10from' => 'F01',
                'icd10to' => 'F09',
                'icd9from' => 290.0,
                'icd9to' => 294.21,
                'contains' => 'dementia',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            9 => 
            array (
                'id' => 11,
                'default_icd_10_code' => 'J45.901',
                'name' => 'Asthma',
                'icd10from' => 'J44.9',
                'icd10to' => 'J45.99',
                'icd9from' => 490.0,
                'icd9to' => 496.0,
                'contains' => 'Asthma, COLD',
                'is_behavioral' => 0,
                'created_at' => '2016-01-27 13:11:24',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            10 => 
            array (
                'id' => 14,
                'default_icd_10_code' => 'F17.299',
                'name' => 'Smoking',
                'icd10from' => 'F17',
                'icd10to' => 'F17.3',
                'icd9from' => 305.1,
                'icd9to' => 305.1,
                'contains' => 'smoking',
                'is_behavioral' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            11 => 
            array (
                'id' => 15,
                'default_icd_10_code' => 'E03.9',
                'name' => 'Acquired Hypothyroidism',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => 'Hypothyroidism',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:43',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            12 => 
            array (
                'id' => 16,
                'default_icd_10_code' => 'I21.9',
                'name' => 'Myocardial Infarction',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:44',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            13 => 
            array (
                'id' => 17,
                'default_icd_10_code' => 'G30.9',
                'name' => 'Alzheimer\'s Disease',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:44',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            14 => 
            array (
                'id' => 18,
                'default_icd_10_code' => 'D64.9',
                'name' => 'Anemia',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:44',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            15 => 
            array (
                'id' => 19,
                'default_icd_10_code' => 'N40.1',
                'name' => 'BPH',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:46',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            16 => 
            array (
                'id' => 20,
                'default_icd_10_code' => 'H25.9',
                'name' => 'Cataract',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:46',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            17 => 
            array (
                'id' => 21,
                'default_icd_10_code' => 'J44.9',
                'name' => 'COPD',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => 'COPD, Chronic Obstructive Pulmonary Disease, Chronic Obstructive Lung Disease',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:51',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            18 => 
            array (
                'id' => 22,
                'default_icd_10_code' => 'H40.9',
                'name' => 'Glaucoma',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:54',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            19 => 
            array (
                'id' => 23,
                'default_icd_10_code' => 'S32.9XXA',
                'name' => 'Hip/Pelvic Fracture',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:44:57',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            20 => 
            array (
                'id' => 24,
                'default_icd_10_code' => 'M81.0',
                'name' => 'Osteoporosis',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:04',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            21 => 
            array (
                'id' => 25,
                'default_icd_10_code' => 'M19.90',
                'name' => 'Arthritis',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:04',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            22 => 
            array (
                'id' => 26,
                'default_icd_10_code' => 'I63.9',
                'name' => 'Stroke',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:11',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            23 => 
            array (
                'id' => 27,
                'default_icd_10_code' => 'C50.919',
                'name' => 'Breast Cancer',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:13',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            24 => 
            array (
                'id' => 28,
                'default_icd_10_code' => 'C18.9',
                'name' => 'Colorectal Cancer',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:13',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            25 => 
            array (
                'id' => 29,
                'default_icd_10_code' => 'Z85.46',
                'name' => 'Prostate Cancer',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:14',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            26 => 
            array (
                'id' => 30,
                'default_icd_10_code' => 'C34.90',
                'name' => 'Lung Cancer',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:14',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            27 => 
            array (
                'id' => 31,
                'default_icd_10_code' => 'C54.1',
                'name' => 'Endometrial Cancer',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-03-06 04:45:14',
                'updated_at' => '2017-10-12 13:19:22',
            ),
            28 => 
            array (
                'id' => 32,
                'default_icd_10_code' => 'E10.8',
                'name' => 'Diabetes Type 1',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-10-12 14:25:00',
                'updated_at' => '2017-10-12 14:25:00',
            ),
            29 => 
            array (
                'id' => 33,
                'default_icd_10_code' => 'E11.8',
                'name' => 'Diabetes Type 2',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2017-10-12 14:25:00',
                'updated_at' => '2017-10-12 14:25:00',
            ),
            30 => 
            array (
                'id' => 34,
                'default_icd_10_code' => '',
                'name' => 'Drug Use Disorder',
                'icd10from' => '',
                'icd10to' => '',
                'icd9from' => 0.0,
                'icd9to' => 0.0,
                'contains' => '',
                'is_behavioral' => 0,
                'created_at' => '2018-02-01 07:32:49',
                'updated_at' => '2018-02-01 07:32:49',
            ),
        );
    }
}