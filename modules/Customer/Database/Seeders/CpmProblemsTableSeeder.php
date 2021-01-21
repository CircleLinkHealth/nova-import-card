<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Database\Seeders;

use Illuminate\Database\Seeder;

class CpmProblemsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('cpm_problems')->delete();

        \DB::table('cpm_problems')->insert([
            0 => [
                'id'                  => 1,
                'default_icd_10_code' => null,
                'name'                => 'Diabetes',
                'icd10from'           => 'E08',
                'icd10to'             => 'E13.0',
                'icd9from'            => 250.0,
                'icd9to'              => 259.93,
                'contains'            => 'diabetes, diabetes mellitus, Disorder Due To Type 2 Diabetes Mellitus',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            1 => [
                'id'                  => 2,
                'default_icd_10_code' => 'I10',
                'name'                => 'Hypertension',
                'icd10from'           => 'I10',
                'icd10to'             => 'I13.11',
                'icd9from'            => 401.0,
                'icd9to'              => 405.0,
                'contains'            => 'Hypertension, Benign Hypertensive Heart Disease, Benign Hypertension, Hypertensive, Hypertensive disorder, Hyper tension',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            2 => [
                'id'                  => 3,
                'default_icd_10_code' => 'I48.91',
                'name'                => 'Afib',
                'icd10from'           => 'I48',
                'icd10to'             => 'I48.99',
                'icd9from'            => 427.0,
                'icd9to'              => 427.89,
                'contains'            => 'atrial fibrillation, paroxysmal supraventricular tachycardia, atrial flutter, sinoatrial node dysfunction',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            3 => [
                'id'                  => 4,
                'default_icd_10_code' => 'I25.9',
                'name'                => 'CAD/IHD',
                'icd10from'           => 'I25',
                'icd10to'             => 'I25.9',
                'icd9from'            => 414.0,
                'icd9to'              => 414.9,
                'contains'            => 'cad, Coronary arteriosclerosis',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            4 => [
                'id'                  => 5,
                'default_icd_10_code' => 'F33.9',
                'name'                => 'Depression',
                'icd10from'           => 'F32.0',
                'icd10to'             => 'F33.9',
                'icd9from'            => 296.0,
                'icd9to'              => 296.99,
                'contains'            => 'Depression, Depressive Disorder, Severe Recurrent Major Depression Without Psychotic Features, Major Depressive Disorder',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2018-06-08 15:29:50',
            ],
            5 => [
                'id'                  => 6,
                'default_icd_10_code' => 'I50.9',
                'name'                => 'CHF',
                'icd10from'           => 'I50.1',
                'icd10to'             => 'I50.9',
                'icd9from'            => 428.0,
                'icd9to'              => 428.9,
                'contains'            => 'Heart failure, congestive heart failure',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            6 => [
                'id'                  => 7,
                'default_icd_10_code' => 'E78.5',
                'name'                => 'High Cholesterol',
                'icd10from'           => 'E78.0',
                'icd10to'             => 'E78.9',
                'icd9from'            => 272.0,
                'icd9to'              => 272.4,
                'contains'            => 'Hyperlipidemia, Cholesterol, High Lipids',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            7 => [
                'id'                  => 8,
                'default_icd_10_code' => 'N18.9',
                'name'                => 'Kidney Disease',
                'icd10from'           => 'N18.0',
                'icd10to'             => 'N18.9',
                'icd9from'            => 585.1,
                'icd9to'              => 585.9,
                'contains'            => 'Chronic Kidney Disease, Kidney Disease',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            8 => [
                'id'                  => 9,
                'default_icd_10_code' => 'F03',
                'name'                => 'Dementia',
                'icd10from'           => 'F01',
                'icd10to'             => 'F09',
                'icd9from'            => 290.0,
                'icd9to'              => 294.21,
                'contains'            => 'dementia',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2018-06-08 15:32:01',
            ],
            9 => [
                'id'                  => 11,
                'default_icd_10_code' => 'J45.901',
                'name'                => 'Asthma',
                'icd10from'           => 'J44.9',
                'icd10to'             => 'J45.99',
                'icd9from'            => 490.0,
                'icd9to'              => 496.0,
                'contains'            => 'Asthma, COLD',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2016-01-27 14:11:24',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            10 => [
                'id'                  => 14,
                'default_icd_10_code' => 'F17.299',
                'name'                => 'Smoking',
                'icd10from'           => 'F17',
                'icd10to'             => 'F17.3',
                'icd9from'            => 305.1,
                'icd9to'              => 305.1,
                'contains'            => 'smoking',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-10-17 10:39:41',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            11 => [
                'id'                  => 15,
                'default_icd_10_code' => 'E03.9',
                'name'                => 'Acquired Hypothyroidism',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'Hypothyroidism',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:43',
                'updated_at'          => '2018-06-26 09:05:02',
            ],
            12 => [
                'id'                  => 16,
                'default_icd_10_code' => 'I21.9',
                'name'                => 'Myocardial Infarction',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:44',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            13 => [
                'id'                  => 17,
                'default_icd_10_code' => 'G30.9',
                'name'                => 'Alzheimer\'s Disease',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:44',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            14 => [
                'id'                  => 18,
                'default_icd_10_code' => 'D64.9',
                'name'                => 'Anemia',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:44',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            15 => [
                'id'                  => 19,
                'default_icd_10_code' => 'N40.1',
                'name'                => 'BPH',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:46',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            17 => [
                'id'                  => 21,
                'default_icd_10_code' => 'J44.9',
                'name'                => 'COPD',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'COPD, Chronic Obstructive Pulmonary Disease, Chronic Obstructive Lung Disease',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:51',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            18 => [
                'id'                  => 22,
                'default_icd_10_code' => 'H40.9',
                'name'                => 'Glaucoma',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:54',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            19 => [
                'id'                  => 23,
                'default_icd_10_code' => 'S32.9XXA',
                'name'                => 'Hip/Pelvic Fracture',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:44:57',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            20 => [
                'id'                  => 24,
                'default_icd_10_code' => 'M81.0',
                'name'                => 'Osteoporosis',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:04',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            21 => [
                'id'                  => 25,
                'default_icd_10_code' => 'M19.90',
                'name'                => 'Arthritis',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:04',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            22 => [
                'id'                  => 26,
                'default_icd_10_code' => 'I63.9',
                'name'                => 'Stroke',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:11',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            23 => [
                'id'                  => 27,
                'default_icd_10_code' => 'C50.919',
                'name'                => 'Breast Cancer',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'cancer - breast',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:13',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            24 => [
                'id'                  => 28,
                'default_icd_10_code' => 'C18.9',
                'name'                => 'Colorectal Cancer',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'cancer - colon, cancer - colorectal',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:13',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            25 => [
                'id'                  => 29,
                'default_icd_10_code' => 'Z85.46',
                'name'                => 'Prostate Cancer',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'Malignant Neoplasm Of Prostate, cancer - prostate, Prostate Neoplasm',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:14',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            26 => [
                'id'                  => 30,
                'default_icd_10_code' => 'C34.90',
                'name'                => 'Lung Cancer',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'cancer - lung, cancer - lungs',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:14',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            27 => [
                'id'                  => 31,
                'default_icd_10_code' => 'C54.1',
                'name'                => 'Endometrial Cancer',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'cancer - endometrial',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-03-06 05:45:14',
                'updated_at'          => '2017-10-17 10:39:42',
            ],
            28 => [
                'id'                  => 32,
                'default_icd_10_code' => 'E10.8',
                'name'                => 'Diabetes Type 1',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-10-17 10:39:41',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            29 => [
                'id'                  => 33,
                'default_icd_10_code' => 'E11.8',
                'name'                => 'Diabetes Type 2',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2017-10-17 10:39:41',
                'updated_at'          => '2017-10-17 10:39:41',
            ],
            30 => [
                'id'                  => 595,
                'default_icd_10_code' => '',
                'name'                => 'Substance Abuse (ex-Alcoholism)',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2018-06-08 15:18:01',
                'updated_at'          => '2018-06-08 15:19:39',
            ],
            31 => [
                'id'                  => 596,
                'default_icd_10_code' => 'F41.1',
                'name'                => 'Anxiety and Stress',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => 'anxiety,stress,some,keywrd',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2018-06-08 15:29:15',
                'updated_at'          => '2018-06-26 09:04:46',
            ],
            32 => [
                'id'                  => 597,
                'default_icd_10_code' => 'F10.20',
                'name'                => 'Alcoholism',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2018-06-08 15:30:32',
                'updated_at'          => '2018-06-08 15:30:32',
            ],
            33 => [
                'id'                  => 598,
                'default_icd_10_code' => 'F31.9',
                'name'                => 'Bipolar',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2018-06-08 15:32:22',
                'updated_at'          => '2018-06-08 15:32:22',
            ],
            34 => [
                'id'                  => 599,
                'default_icd_10_code' => '',
                'name'                => 'Psychosis & Schizophrenia',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2018-06-08 15:33:34',
                'updated_at'          => '2018-06-08 15:33:34',
            ],
            35 => [
                'id'                  => 600,
                'default_icd_10_code' => 'F43.10',
                'name'                => 'Post-traumatic stress',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 1,
                'weight'              => 1,
                'created_at'          => '2018-06-08 15:34:22',
                'updated_at'          => '2018-06-08 15:34:22',
            ],
            36 => [
                'id'                  => 601,
                'default_icd_10_code' => 'E66.9',
                'name'                => 'Obesity',
                'icd10from'           => '',
                'icd10to'             => '',
                'icd9from'            => 0.0,
                'icd9to'              => 0.0,
                'contains'            => '',
                'is_behavioral'       => 0,
                'weight'              => 1,
                'created_at'          => '2019-05-11 15:34:22',
                'updated_at'          => '2019-05-11 15:34:22',
            ],
        ]);

        $this->command->info('cpm problems seeded');
    }
}