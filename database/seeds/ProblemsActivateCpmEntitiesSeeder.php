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
        $cpmProblems = \App\Models\CPM\CpmProblem::all();
        $defaultData = $this->getDefaultData();
        $defaultTemplate = \App\CarePlanTemplate::whereType('CLH Default')->first();

        /**
         * Wow, brah. That's cray. #3-nested-loops #killing-dat-big-O :joy:
         */
        foreach ($cpmProblems as $problem) {
            $problemDefaults = $defaultData[$problem->name];

            foreach ($problemDefaults as $relationship => $data) {
                
                $type = app($data['type']);
                $values = $data['values'];
                
                if (empty($values)) continue;
                
                foreach ($values as $value) {
                    $relObj = $type->whereName($value)->first();
                    
                    if ($relObj)
                    {
                        $this->command->info("Found {$relObj->name}");

                        try {
                            $problem->{$relationship}()->attach($relObj->id, [
                                'care_plan_template_id' => $defaultTemplate->id,
                            ]);
                        } catch (Illuminate\Database\QueryException $e){
                            $errorCode = $e->errorInfo[1];
                            if($errorCode == 1062){
                                $this->command->error("\tRelationship already already exists, so it won't be added again.\t");
                            }
                        }
                    }
                }
            }
        }
    }

    public function getDefaultData()
    {
        return [
            'Diabetes' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => ['Blood Sugar']
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => ['Diabetic Diet'],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => ['Oral Diabetes Meds'],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
                        'Weakness/dizziness',
                        'Shortness of breath',
                        'Chest pain/tightness',
                        'Fatigue',
                        'Pain'
                    ],
                ],

            ],

            'Hypertension' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => ['Blood Pressure'],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => ['Low Salt Diet'],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => ['Blood Pressure Meds'],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
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
            ],

            'Afib' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => ['Blood Pressure'],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => [],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => [],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
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
            ],

            'CAD' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => ['Blood Pressure'],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => ['Healthy Diet'],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => [],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
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
            ],

            'Depression' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => [],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => [],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => ['Mood/Depression Meds'],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
                        'Sweating',
                        'Palpitations',
                        'Anxiety',
                        'Feeling down/sleep changes',
                    ],
                ],
            ],

            'CHF' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => ['Blood Pressure', 'Weight'],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => [],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => [],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
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
            ],

            'High Cholesterol' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => [],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => ['Healthy Diet'],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => [],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
                        'Shortness of breath',
                        'Chest pain/tightness',
                    ],
                ],
            ],

            'Kidney Disease' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => ['Blood Pressure'],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => ['Healthy Diet'],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => ['Kidney Disease Meds'],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
                        'Shortness of breath',
                        'Coughing/wheezing',
                        'Fatigue',
                        'Weakness/dizziness',
                        'Swelling in legs/feet',
                        'Chest pain/tightness',
                    ]
                ],
            ],

            'Dementia' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => [],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => [],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => ['Dementia Meds'],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [],
                ],
            ],

            'Asthma--COPD' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => [],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => [],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => ['Breathing Meds for Asthma/COPD'],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [
                        'Shortness of breath',
                        'Coughing/wheezing',
                        'Chest pain/tightness',
                        'Fatigue',
                        'Weakness/dizziness',
                        'Palpitations',
                        'Anxiety',
                    ],
                ]
            ],

            'Smoking' => [

                'cpmBiometricsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmBiometric::class,
                    'values' => [],
                ],

                'cpmLifestylesToBeActivated' => [
                    'type' => \App\Models\CPM\CpmLifestyle::class,
                    'values' => [],
                ],

                'cpmMedicationGroupsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmMedicationGroup::class,
                    'values' => [],
                ],

                'cpmSymptomsToBeActivated' => [
                    'type' => \App\Models\CPM\CpmSymptom::class,
                    'values' => [],
                ],
            ],

        ];
    }
}
