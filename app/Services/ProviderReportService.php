<?php


namespace App\Services;


class ProviderReportService
{
    public function formatReportDataForView($report)
    {
        $demographicData['age']    = $this->getStringValue($report->demographic_data['age'], 'N/A');
        $demographicData['race']   = ucwords(strtolower($this->getStringValue($report->demographic_data['race'],
            'N/A')));
        $demographicData['gender'] = strtolower($this->getStringValue($report->demographic_data['gender'], 'N/A'));
        $demographicData['health'] = strtolower($this->getStringValue($report->demographic_data['health'], 'N/A'));

        $allergyHistory = [];
        if ( ! empty($report->allergy_history)) {
            foreach ($report->allergy_history as $allergy) {
                $allergyHistory[] = strtolower($allergy['name']);
            }
        }

        $medicalHistoryConditions = [];
        if ( ! empty($report->medical_history['conditions'])) {
            foreach ($report->medical_history['conditions'] as $condition) {
                $conditionData              = [
                    'name' => ucwords(strtolower($condition['name'])),
                    'type' => $condition['type']
                        ? ucwords(strtolower($condition['type']))
                        : '',
                ];
                $medicalHistoryConditions[] = $conditionData;
            }
        }

        $medicalHistoryOther = [];
        if ( ! empty($report->medical_history['other_conditions'])) {
            foreach ($report->medical_history['other_conditions'] as $condition) {
                if ( ! empty($condition) && ! empty($condition['name'])) {
                    $medicalHistoryOther[] = ucwords(strtolower($condition['name']));
                }
            }
        }

        $medicationHistory = [];
        if ( ! empty($report->medication_history)) {
            foreach ($report->medication_history as $medication) {
                if ( ! empty($medication)) {
                    $medicationData = [
                        'dose'      => $medication['dose'],
                        'drug'      => ucwords(strtolower($medication['drug'])),
                        'frequency' => strtolower($medication['frequency']),
                    ];

                    $medicationHistory[] = $medicationData;
                }
            }
        }

        $familyConditions = [];
        if ( ! empty($report->family_medical_history)) {
            foreach ($report->family_medical_history as $condition) {
                if ( ! empty($condition) && ! empty($condition['name'])) {
                    $conditionData      = [
                        'name'   => ucwords(strtolower($condition['name'])),
                        'family' => strtolower(implode(', ', $condition['family'])),
                    ];
                    $familyConditions[] = $conditionData;
                }
            }
        }

        $immunizationsReceived    = [];
        $immunizationsNotReceived = [];
        if ( ! empty($report->immunization_history)) {
            foreach ($report->immunization_history as $immunization => $received) {
                if (strtolower($received) === 'yes') {
                    $immunizationsReceived[] = $immunization;
                } else {
                    $immunizationsNotReceived[] = $immunization;
                }
            }
        }

        $screenings = [];
        if ( ! empty($report->screenings)) {
            if ( ! empty($report->screenings['breast_cancer'] && $report->screenings['breast_cancer'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Breast cancer'] = " (Mammogram): Had " . $report->screenings['breast_cancer'] . '.';
            }
            if ( ! empty($report->screenings['cervical_cancer'] && $report->screenings['cervical_cancer'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Cervical cancer'] = " (Pap smear): Had " . $report->screenings['cervical_cancer'] . '.';
            }
            if ( ! empty($report->screenings['colorectal_cancer'] && $report->screenings['colorectal_cancer'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Colorectal cancer'] = " (e.g. Fecal Occult Blood Test (FOBT), Fecal Immunohistochemistry Test (FIT) Sigmoidoscopy, Colonoscopy): Had " . $report->screenings['colorectal_cancer'] . '.';
            }
            if ( ! empty($report->screenings['skin_cancer'] && $report->screenings['skin_cancer'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Skin cancer'] = ": Had " . $report->screenings['skin_cancer'] . '.';
            }
            if ( ! empty($report->screenings['prostate_cancer'] && $report->screenings['prostate_cancer'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Prostate cancer'] = " (Prostate screening Test): Had " . $report->screenings['prostate_cancer'] . '.';
            }
            if ( ! empty($report->screenings['glaucoma'] && $report->screenings['glaucoma'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Glaucoma'] = ": Had " . $report->screenings['glaucoma'] . '.';
            }
            if ( ! empty($report->screenings['osteoporosis'] && $report->screenings['osteoporosis'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Osteoporosis'] = " (Bone Density Test): Had " . $report->screenings['osteoporosis'] . '.';
            }
            if ( ! empty($report->screenings['violence'] && $report->screenings['violence'] !== '10+ years ago/Never/Unsure')) {
                $screenings['Intimate Partner Violence/Domestic Violence'] = ": Had " . $report->screenings['violence'] . '.';
            }
        }

        $mentalState = [];
        if ( ! empty($report->mental_state['depression_score']) || $report->mental_state['depression_score'] === 0) {
            $mentalState['score'] = $report->mental_state['depression_score'];
            $diagnosis            = 'no depression';
            if ($report->mental_state['depression_score'] > 2) {
                $diagnosis = 'potential depression - further testing may be required';
            }
            $mentalState['diagnosis'] = $diagnosis;
        }

        $vitals = [];

        if ( ! empty($report->vitals['blood_pressure'])) {
            $vitals['blood_pressure']['first_metric']  = (int)$report->vitals['blood_pressure']['first_metric'] !== 0
                ? $report->vitals['blood_pressure']['first_metric']
                : 'N/A';
            $vitals['blood_pressure']['second_metric'] = (int)$report->vitals['blood_pressure']['second_metric'] !== 0
                ? $report->vitals['blood_pressure']['second_metric']
                : 'N/A';
        }
        if ( ! empty($report->vitals['bmi'])) {

            $bmiVal = $this->getStringValue($report->vitals['bmi']);

            $vitals['bmi'] = $bmiVal;
            if ((float)$bmiVal < 18.5) {
                $vitals['bmi_diagnosis']  = 'low';
                $vitals['body_diagnosis'] = 'underweight';
            } elseif ((float)$bmiVal > 25) {
                $vitals['bmi_diagnosis']  = 'high';
                $vitals['body_diagnosis'] = (float)$bmiVal < 30
                    ? 'obese'
                    : 'overweight';
            } else {
                $vitals['bmi_diagnosis']  = 'normal';
                $vitals['body_diagnosis'] = 'normal';
            }
        } else {
            $vitals['bmi']           = 'N/A';
            $vitals['bmi_diagnosis'] = 'N/A';
        }


        $vitals['height']['feet']   = (int)$report->vitals['height']['feet'] !== 0
            ? $report->vitals['height']['feet']
            : 'N/A';
        $vitals['height']['inches'] = (int)$report->vitals['height']['inches'] !== 0
            ? $report->vitals['height']['inches']
            : 'N/A';


        $vitals['weight'] = $this->getStringValue($report->vitals['weight'], 'N/A');


        $diet = [];
        if ( ! empty($report->diet)) {
            $diet['fried_fatty']       = $this->getStringValue($report->diet['fried_fatty']);
            $diet['grain_fiber']       = $this->getStringValue($report->diet['grain_fiber']);
            $diet['sugary_beverages']  = $this->getStringValue($report->diet['sugary_beverages']);
            $diet['fruits_vegetables'] = $this->getStringValue($report->diet['fruits_vegetables']);
            if ( ! empty($report->diet['change_in_diet'])) {

                $change = $this->getStringValue($report->diet['change_in_diet']);

                $diet['have_changed_diet'] = strtolower($change) === 'yes'
                    ? 'have'
                    : 'have not';
            }
        }

        $functionalCapacity                                 = [];
        $functionalCapacity['has_fallen']                   = strtolower($this->getStringValue($report->functional_capacity['has_fallen'])) === 'yes'
            ? 'has'
            : 'has not';
        $functionalCapacity['have_assistance']              = strtolower($this->getStringValue($report->functional_capacity['have_assistance'])) === 'yes'
            ? 'do'
            : 'do not';
        $functionalCapacity['hearing_difficulty']           = strtolower($this->getStringValue($report->functional_capacity['hearing_difficulty'])) === 'yes'
            ? 'has'
            : (strtolower($this->getStringValue($report->functional_capacity['hearing_difficulty'])) === 'sometimes'
                ? 'sometimes has'
                : 'does not have');
        $functionalCapacity['mci_cognitive']['clock']       = $report->functional_capacity['mci_cognitive']['clock'] == 2
            ? 'able'
            : 'unable';
        $functionalCapacity['mci_cognitive']['word_recall'] = $report->functional_capacity['mci_cognitive']['word_recall'];
        $functionalCapacity['mci_cognitive']['total']       = $report->functional_capacity['mci_cognitive']['total'];
        $functionalCapacity['mci_cognitive']['diagnosis']   = $report->functional_capacity['mci_cognitive']['total'] > 3
            ? 'no cognitive impairment'
            : ($report->functional_capacity['mci_cognitive']['total'] == 3
                ? 'mild cognitive impairment'
                : 'dementia');
        if ( ! empty($report->functional_capacity['needs_help_for_tasks'])) {
            foreach ($report->functional_capacity['needs_help_for_tasks'] as $task) {
                $functionalCapacity['needs_help_for_tasks'][] = strtolower($task['name']);
            }
        } else {
            $functionalCapacity['needs_help_for_tasks'] = [];
        }

        $currentProviders = [];
        if ( ! empty($report->current_providers)) {
            foreach ($report->current_providers as $provider) {
                $providerData       = [
                    'provider_name' => ! empty($provider['provider_name'])
                        ? ucwords(strtolower($provider['provider_name']))
                        : 'N/A',
                    'location'      => ! empty($provider['location'])
                        ? ucwords(strtolower($provider['location']))
                        : 'N/A',
                    'specialty'     => ! empty($provider['specialty'])
                        ? ucwords(strtolower($provider['specialty']))
                        : 'N/A',
                    'phone_number'  => ! empty($provider['phone_number'])
                        ? $provider['phone_number']
                        : 'N/A',
                ];
                $currentProviders[] = $providerData;
            }
        }

        $advancedCarePlanning                  = [];
        $advancedCarePlanning['living_will']   = strtolower($this->getStringValue($report->advanced_care_planning['living_will']));
        $advancedCarePlanning['has_attorney']  = strtolower($this->getStringValue($report->advanced_care_planning['has_attorney'])) === 'yes'
            ? 'has'
            : 'does not have';
        $advancedCarePlanning['existing_copy'] = strtolower($this->getStringValue($report->advanced_care_planning['existing_copy'])) === 'yes'
            ? 'is'
            : 'is not';


        return collect([
            'reason_for_visit'           => $report->reason_for_visit,
            'demographic_data'           => $demographicData,
            'allergy_history'            => $allergyHistory,
            'medical_history'            => $medicalHistoryConditions,
            'medical_history_other'      => $medicalHistoryOther,
            'medication_history'         => $medicationHistory,
            'family_medical_history'     => $familyConditions,
            'immunizations_received'     => $immunizationsReceived,
            'immunizations_not_received' => $immunizationsNotReceived,
            'screenings'                 => $screenings,
            'mental_state'               => $mentalState,
            'vitals'                     => $vitals,
            'diet'                       => $diet,
            'social_factors'             => $report->social_factors,
            'sexual_activity'            => $report->sexual_activity,
            'exercise_activity_levels'   => $report->exercise_activity_levels,
            'functional_capacity'        => $functionalCapacity,
            'current_providers'          => $currentProviders,
            'advanced_care_planning'     => $advancedCarePlanning,
            'specific_patient_requests'  => $this->getStringValue($report->specific_patient_requests),
        ]);
    }

    public static function getStringValue($val, $default = '')
    {

        if (empty($val)) {
            return $default;
        }

        if (is_string($val)) {
            return $val;
        }

        if (is_array($val)) {

            if (array_key_exists('name', $val)) {
                return self::getStringValue($val['name']);
            }

            if (array_key_exists('value', $val)) {
                return self::getStringValue($val['value']);
            }

            return self::getStringValue($val[0]);
        }

        return $val;
    }

}
