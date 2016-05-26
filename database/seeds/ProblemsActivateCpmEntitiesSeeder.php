<?php

use Illuminate\Database\Seeder;

class ProblemsActivateCpmEntitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultTemplate = \App\CarePlanTemplate::whereType('type')->first();


    }

    public function getDefaultData()
    {
        return [
            'Diabetes' => [
                'cpmBiometricsToBeActivated' => ['Blood Sugar'],
                'cpmLifestylesToBeActivated' => ['Diabetic Diet'],
                'cpmMedicationGroupsToBeActivated' => ['Oral Diabetes Meds'],
                'cpmSymptomsToBeActivated' => [
                    'Weakness/dizziness',
                    'Shortness of breath',
                    'Chest pain/tightness',
                    'Fatigue',
                    'Pain'
                ],
            ],

            'Hypertension' => [
                'cpmBiometricsToBeActivated' => ['Blood Pressure'],
                'cpmLifestylesToBeActivated' => ['Low Salt Diet'],
                'cpmMedicationGroupsToBeActivated' => ['Blood Pressure Meds'],
                'cpmSymptomsToBeActivated' => [
                    'Shortness of breath',
                    'Coughing/wheezing',
                    'Chest pain/tightness',
                    'Fatigue',
                    'Weakness/dizziness',
                    'Swelling in legs/feet',
                    'Sweating',
                    'Palpitations',
                    'Anxiety',
                ],
            ],

            'Afib' => [
                'cpmBiometricsToBeActivated' => ['Blood Pressure'],
                'cpmLifestylesToBeActivated' => [],
                'cpmMedicationGroupsToBeActivated' => [],
                'cpmSymptomsToBeActivated' => [
                    'Shortness of breath',
                    'Coughing/wheezing',
                    'Chest pain/tightness',
                    'Fatigue',
                    'Weakness/dizziness',
                    'Swelling in legs/feet',
                    'Sweating',
                    'Palpitations',
                    'Anxiety',
                ],
            ],

            'CAD' => [
                'cpmBiometricsToBeActivated' => ['Blood Pressure'],
                'cpmLifestylesToBeActivated' => ['Healthy Diet'],
                'cpmMedicationGroupsToBeActivated' => [],
                'cpmSymptomsToBeActivated' => [
                    'Shortness of breath',
                    'Coughing/wheezing',
                    'Chest pain/tightness',
                    'Fatigue',
                    'Weakness/dizziness',
                    'Swelling in legs/feet',
                    'Sweating',
                    'Palpitations',
                    'Anxiety',
                ],
            ],

            'Depression' => [
                'cpmBiometricsToBeActivated' => [],
                'cpmLifestylesToBeActivated' => [],
                'cpmMedicationGroupsToBeActivated' => ['Mood/Depression Meds'],
                'cpmSymptomsToBeActivated' => [
                    'Sweating',
                    'Palpitations',
                    'Anxiety',
                    'Feeling down/sleep changes',
                ],
            ],

            'CHF' => [
                'cpmBiometricsToBeActivated' => ['Blood Pressure', 'Weight'],
                'cpmLifestylesToBeActivated' => [],
                'cpmMedicationGroupsToBeActivated' => [],
                'cpmSymptomsToBeActivated' => [
                    'Shortness of breath',
                    'Coughing/wheezing',
                    'Chest pain/tightness',
                    'Fatigue',
                    'Weakness/dizziness',
                    'Swelling in legs/feet',
                    'Sweating',
                    'Palpitations',
                    'Anxiety',
                ],
            ],

            'High Cholesterol' => [
                'cpmBiometricsToBeActivated' => [],
                'cpmLifestylesToBeActivated' => ['Healthy Diet'],
                'cpmMedicationGroupsToBeActivated' => [],
                'cpmSymptomsToBeActivated' => [
                    'Shortness of breath',
                    'Chest pain/tightness',
                ],
            ],

            'Kidney Disease' => [
                'cpmBiometricsToBeActivated' => ['Blood Pressure'],
                'cpmLifestylesToBeActivated' => ['Healthy Diet'],
                'cpmMedicationGroupsToBeActivated' => ['Kidney Disease Meds'],
                'cpmSymptomsToBeActivated' => [
                    'Shortness of breath',
                    'Coughing/wheezing',
                    'Fatigue',
                    'Weakness/dizziness',
                    'Swelling in legs/feet',
                    'Chest pain/tightness',
                ],
            ],

            'Dementia' => [
                'cpmBiometricsToBeActivated' => [],
                'cpmLifestylesToBeActivated' => [],
                'cpmMedicationGroupsToBeActivated' => ['Dementia Meds'],
                'cpmSymptomsToBeActivated' => [],
            ],

            'Asthma--COPD' => [
                'cpmBiometricsToBeActivated' => [],
                'cpmLifestylesToBeActivated' => [],
                'cpmMedicationGroupsToBeActivated' => ['Breathing Meds for Asthma/COPD'],
                'cpmSymptomsToBeActivated' => [
                    'Shortness of breath',
                    'Coughing/wheezing',
                    'Chest pain/tightness',
                    'Fatigue',
                    'Weakness/dizziness',
                    'Palpitations',
                    'Anxiety',
                ],
            ],

            'Smoking' => [
                'cpmBiometricsToBeActivated' => [],
                'cpmLifestylesToBeActivated' => [],
                'cpmMedicationGroupsToBeActivated' => [],
                'cpmSymptomsToBeActivated' => [],
            ],

        ];
    }
}
