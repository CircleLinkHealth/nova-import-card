<?php

namespace App\Services;

use Exception;

class ProviderReportService
{
    const YES = 'Yes';
    const NO = 'No';

    /**
     * @return array
     */
    public static function depressionScoreArray()
    {
        return [
            'not at all'              => 0,
            'several days'            => 1,
            'more than half the days' => 2,
            'nearly every day'        => 3,
        ];
    }

    //@todo: At some point move all helper functions to a different file

    public static function getArrayValue($val, $default = [])
    {
        if (! is_array($val)) {
            return $default;
        }

        $filtered = array_filter($val, function ($v) {
            if (is_array($v)) {
                return ! empty(self::getArrayValue($v));
            } else {
                return ! self::filterAnswer($v);
            }
        });

        if (empty($filtered)) {
            return $default;
        } else {
            //return all array so we don't break structure
            return $val;
        }
    }

    /**
     * if the result of this function === true THEN will throw exception from: validateOptionalAnswers().
     *
     * @param $var
     *
     * @return bool
     */
    public static function filterAnswer($var)
    {
        return $var === '' || $var === [] || $var === null;
    }

    /**
     * @param $report
     *
     * @return \Illuminate\Support\Collection
     * @throws Exception
     */
    public function formatReportDataForView($report)
    {
        $demographicData['age'] = getStringValueFromAnswer($report->demographic_data['age'], 'N/A');
        $demographicData['race'] = ucwords(strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['race'],
            'race', [])));
        $demographicData['ethnicity'] = ucwords(strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['ethnicity'],
            'ethnicity', [])));
        $demographicData['gender'] = strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['gender'],
            'gender', []));
        $demographicData['health'] = strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['health'],
            'health', []));

        $allergyHistory = [];
        if (! self::filterAnswer($report->allergy_history)) {
            foreach ($report->allergy_history as $allergy) {
                $allergyHistory[] = strtolower($allergy['name']);
            }
        }

        $medicalHistoryConditions = [];
        if (! self::filterAnswer($report->medical_history['conditions'])) {
            foreach ($report->medical_history['conditions'] as $condition) {
                $conditionData = [
                    'name' => ucwords(strtolower($condition['name'])),
                    'type' => $condition['type']
                        ? ucwords(strtolower($condition['type']))
                        : '',
                ];
                $medicalHistoryConditions[] = $conditionData;
            }
        }

        $medicalHistoryOther = [];
        if (! self::filterAnswer($report->medical_history['other_conditions'])) {
            foreach ($report->medical_history['other_conditions'] as $condition) {
                if (! self::filterAnswer($condition) && ! self::filterAnswer($condition['name'])) {
                    $medicalHistoryOther[] = ucwords(strtolower($condition['name']));
                }
            }
        }

        $medicationHistory = [];
        if (! self::filterAnswer($report->medication_history)) {
            foreach ($report->medication_history as $medication) {
                if (! self::filterAnswer($medication)) {
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
        if (! self::filterAnswer($report->family_medical_history)) {
            foreach ($report->family_medical_history as $condition) {
                if (! self::filterAnswer($condition) && ! self::filterAnswer($condition['name'])) {
                    $conditionData = [
                        'name'   => ucwords(strtolower($condition['name'])),
                        'family' => strtolower(implode(', ', $condition['family'])),
                    ];
                    $familyConditions[] = $conditionData;
                }
            }
        }

        $immunizationsReceived = [];
        $immunizationsNotReceived = [];
        if (! self::filterAnswer($report->immunization_history)) {
            foreach ($report->immunization_history as $immunization => $answer) {
                $received = $this->checkInputValueIsNotEmpty($answer, $immunization, $report->demographic_data);
                //Q32 is not always answered.
                if (empty($received)) {
                    continue;
                }
                strtolower($received) === 'yes'
                    ? $immunizationsReceived[] = $immunization
                    : $immunizationsNotReceived[] = $immunization;
            }
        }

        $screenings = [];
        if (! self::filterAnswer($report->screenings)) {
            $gender = $report->demographic_data['gender'];
            $age = getStringValueFromAnswer($report->demographic_data['age']);
            $conditionsSelectedInQ18 = collect($report->family_medical_history)->map(function ($condition) {
                return $condition['name'];
            })->toArray();

            $breastCancer = getStringValueFromAnswer($report->screenings['breast_cancer']);
            $breastCancerSelectedInQ18 = in_array('Breast Cancer', $conditionsSelectedInQ18);
            if (! empty($breastCancer)) {
                if ($gender !== 'Male' && '50' < $age && $age < '74') {
                    $screenings['Breast cancer'] = ' (Mammogram): Had '.$breastCancer.'.';
                } elseif (! ($breastCancer === 'In the last 2-3 years' || $breastCancer === 'In the last year')
                           && $gender !== 'Male'
                           && $breastCancerSelectedInQ18 === true) {
                    $screenings['Breast cancer'] = ' (Mammogram): Had '.$breastCancer.'.';
                } elseif ($breastCancer !== 'In the last year'
                          && $gender !== 'Male'
                          && $breastCancerSelectedInQ18 === true) {
                    $screenings['Breast cancer'] = ' (Mammogram): Had '.$breastCancer.'.';
                }
            }

            $cervicalCancer = getStringValueFromAnswer($report->screenings['cervical_cancer']);
            if (! empty($cervicalCancer)) {
                if ($gender === 'Female'
                    && '21' <= $age
                    && $age <= '29'
                    && $cervicalCancer !== 'In the last 2-3 years') {
                    $screenings['Cervical cancer'] = ' (Pap smear): Had '.$cervicalCancer.'.';
                } elseif ($gender !== 'Male'
                          && '21' <= $age
                          && $age <= '29'
                          && $cervicalCancer !== 'In the last year') {
                    $screenings['Cervical cancer'] = ' (Pap smear): Had '.$cervicalCancer.'.';
                } elseif ($gender !== 'Male'
                          && '30' <= $age
                          && $age <= '65'
                          && $cervicalCancer === 'In the last 6-10 years') {
                    $screenings['Cervical cancer'] = ' (Pap smear): Had '.$cervicalCancer.'.';
                } elseif ($gender !== 'Male'
                          && '30' <= $age
                          && $age <= '65'
                          && $cervicalCancer === '10+ years ago/Never/Unsure') {
                    $screenings['Cervical cancer'] = ' (Pap smear): Had '.$cervicalCancer.'.';
                }
            }

            $colorectalCancer = self::checkInputValueIsNotEmpty($report->screenings['colorectal_cancer'],
                'colorectal_cancer', []);
            $colorectalCancerSelectedInQ18 = in_array('Colorectal Cancer', $conditionsSelectedInQ18);
            if (! empty($colorectalCancer)) {
                if (('50' <= $age && $age <= '75')
                    || $colorectalCancerSelectedInQ18 === true
                    || $colorectalCancer === 'In the last 6-10 years'
                    || $colorectalCancer === 'Never/10 years ago') {
                    $screenings['Colorectal cancer'] = ' (e.g. Fecal Occult Blood Test (FOBT), Fecal Immunohistochemistry Test (FIT) Sigmoidoscopy, Colonoscopy): Had '.$colorectalCancer.'.';
                }
            }

            $skinCancer = self::checkInputValueIsNotEmpty($report->screenings['skin_cancer'],
                'skin_cancer', []);
            $patientCheckedConditionInQ16 = collect($report->medical_history['conditions'])->map(function (
                $condition
            ) {
                $comparison = self::caseInsensitiveComparison($condition['type'], 'skin');

                return $condition['name'] === 'Cancer' && $comparison;
            });
            $skinCancerSelectedInQ18 = in_array('Skin Cancer', $conditionsSelectedInQ18);
            $familyMembersWithSkinCancer = collect($report->family_medical_history)->firstWhere('name', '=',
                'Skin Cancer');
            $countFamilyMembersWithSkinCancer = $familyMembersWithSkinCancer !== null
                ? count($familyMembersWithSkinCancer['family'])
                : 0;
            if (! empty($skinCancer) && ! empty($countFamilyMembersWithSkinCancer)) {
                if (($skinCancerSelectedInQ18 === true && $countFamilyMembersWithSkinCancer >= '2') || $patientCheckedConditionInQ16 === true) {
                    $screenings['Skin cancer screening'] = ': Had '.$skinCancer.'.';
                }
            }

            $prostateCancer = getStringValueFromAnswer($report->screenings['prostate_cancer']);
            $race = $report->demographic_data['race'];
            $prostateCancerSelectedInQ18 = in_array('Prostate Cancer', $conditionsSelectedInQ18);
            if (! empty($prostateCancer)) {
                if ($gender !== 'Female'
                    && '55' <= $age
                    && $age <= '69'
                    && $prostateCancer === '10+ years ago/Never/Unsure') {
                    $screenings['Prostate cancer'] = ' (Prostate screening Test): Had '.$prostateCancer.'.';
                } elseif ($gender !== 'Female'
                          && $race === 'Black/African-Ameri.'
                          && $prostateCancer === '10+ years ago/Never/Unsure') {
                    $screenings['Prostate cancer'] = ' (Prostate screening Test): Had '.$prostateCancer.'.';
                } elseif ($gender !== 'Female' && $prostateCancerSelectedInQ18 === true) {
                    $screenings['Prostate cancer'] = ' (Prostate screening Test): Had '.$prostateCancer.'.';
                }
            }

            $glaucoma = self::checkInputValueIsNotEmpty($report->screenings['glaucoma'], 'glaucoma', []);
            if (! empty($glaucoma) && $glaucoma !== 'In the last year') {
                $screenings['Glaucoma'] = ': Had '.$glaucoma.'.';
            }

            $osteoporosis = self::checkInputValueIsNotEmpty($report->screenings['osteoporosis'], 'osteoporosis', []);
            $answerToQ24 = $report->functional_capacity['has_fallen'];
            if (! empty($osteoporosis)) {
                if ($gender === 'Female'
                    && $age >= '65'
                    && $osteoporosis !== 'In the last year') {
                    $screenings['Osteoporosis'] = ' (Bone Density Test): Had '.$osteoporosis.'.';
                } elseif ($age === 'Male'
                          && $age >= '70'
                          && $osteoporosis !== 'In the last year') {
                    $screenings['Osteoporosis'] = ' (Bone Density Test): Had '.$osteoporosis.'.';
                } elseif ($answerToQ24 === 'Yes'
                          && $osteoporosis !== 'In the last year') {
                    $screenings['Osteoporosis'] = ' (Bone Density Test): Had '.$osteoporosis.'.';
                }
            }

            $violence = getStringValueFromAnswer($report->screenings['violence']);

            if (! empty($violence) && $gender === 'Female') {
                if ($violence === '10+ years ago/Never/Unsure'
                    && '15' <= $age
                    && $age <= '44') {
                    $screenings['Intimate Partner Violence/Domestic Violence'] = ': Had '.$violence.'.';
                }
            }
        }

        $mentalState = [];
        if (! self::filterAnswer($report->mental_state['depression_score']) || $report->mental_state['depression_score'] === 0) {
            $mentalState['score'] = $report->mental_state['depression_score'];
            $diagnosis = 'no depression';
            if ($report->mental_state['depression_score'] > 2) {
                $diagnosis = 'potential depression - further testing may be required';
            }
            $mentalState['diagnosis'] = $diagnosis;
        }

        $vitals = [];

        if (! self::filterAnswer($report->vitals['blood_pressure'])) {
            $vitals['blood_pressure']['first_metric'] = (int) $report->vitals['blood_pressure']['first_metric'] !== 0
                ? $report->vitals['blood_pressure']['first_metric']
                : 'N/A';
            $vitals['blood_pressure']['second_metric'] = (int) $report->vitals['blood_pressure']['second_metric'] !== 0
                ? $report->vitals['blood_pressure']['second_metric']
                : 'N/A';
        }
        if (! self::filterAnswer($report->vitals['bmi'])) {
            $bmiVal = getStringValueFromAnswer($report->vitals['bmi']);

            $vitals['bmi'] = $bmiVal;
            if ((float) $bmiVal < 18.5) {
                $vitals['bmi_diagnosis'] = 'low';
                $vitals['body_diagnosis'] = 'underweight';
            } elseif ((float) $bmiVal > 25) {
                $vitals['bmi_diagnosis'] = 'high';
                $vitals['body_diagnosis'] = (float) $bmiVal < 30
                    ? 'obese'
                    : 'overweight';
            } else {
                $vitals['bmi_diagnosis'] = 'normal';
                $vitals['body_diagnosis'] = 'normal';
            }
        } else {
            $vitals['bmi'] = 'N/A';
            $vitals['bmi_diagnosis'] = 'N/A';
        }

        $vitals['height']['feet'] = (int) $report->vitals['height']['feet'] !== 0
            ? $report->vitals['height']['feet']
            : 'N/A';
        $vitals['height']['inches'] = (int) $report->vitals['height']['inches'] !== 0
            ? $report->vitals['height']['inches']
            : 'N/A';

        $vitals['weight'] = getStringValueFromAnswer($report->vitals['weight'], 'N/A');

        $diet = [];
        if (! self::filterAnswer($report->diet)) {
            $diet['fried_fatty'] = $this->checkInputValueIsNotEmpty($report->diet['fried_fatty'], 'fried_fatty',
                []);
            $diet['grain_fiber'] = $this->checkInputValueIsNotEmpty($report->diet['grain_fiber'], 'grain_fiber',
                []);
            $diet['sugary_beverages'] = $this->checkInputValueIsNotEmpty($report->diet['sugary_beverages'],
                'sugary_beverages', []);
            $diet['fruits_vegetables'] = $this->checkInputValueIsNotEmpty($report->diet['fruits_vegetables'],
                'fruits_vegetables', []);
            $changeInDiet = self::checkInputValueIsNotEmpty($report->diet['change_in_diet'],
                'change_in_diet', []);
            $change = $changeInDiet;

            $diet['have_changed_diet'] = strtolower($change) === 'yes'
                ? 'have'
                : 'have not';
        }

        $functionalCapacity = [];
        $functionalCapacity['has_fallen'] = strtolower($this->checkInputValueIsNotEmpty($report->functional_capacity['has_fallen'],
            'has_fallen', [])) === 'yes'
            ? 'has'
            : 'has not';
        $functionalCapacity['have_assistance'] = strtolower(getStringValueFromAnswer($report->functional_capacity['have_assistance'])) === 'yes'
            ? 'do'
            : 'do not';
        $functionalCapacity['hearing_difficulty'] = strtolower($this->checkInputValueIsNotEmpty($report->functional_capacity['hearing_difficulty'],
            'hearing_difficulty', [])) === 'yes'
            ? 'has'
            : (strtolower(getStringValueFromAnswer($report->functional_capacity['hearing_difficulty'])) === 'sometimes'
                ? 'sometimes has'
                : 'does not have');
        $functionalCapacity['mci_cognitive']['clock'] = $report->functional_capacity['mci_cognitive']['clock'] == 2
            ? 'able'
            : 'unable';
        $functionalCapacity['mci_cognitive']['word_recall'] = $report->functional_capacity['mci_cognitive']['word_recall'];
        $functionalCapacity['mci_cognitive']['total'] = $report->functional_capacity['mci_cognitive']['total'];
        $functionalCapacity['mci_cognitive']['diagnosis'] = $report->functional_capacity['mci_cognitive']['total'] > 3
            ? 'no cognitive impairment'
            : ($report->functional_capacity['mci_cognitive']['total'] == 3
                ? 'mild cognitive impairment'
                : 'dementia');
        if (! self::filterAnswer($report->functional_capacity['needs_help_for_tasks'])) {
            foreach ($report->functional_capacity['needs_help_for_tasks'] as $task) {
                $functionalCapacity['needs_help_for_tasks'][] = strtolower($task['name']);
            }
        } else {
            $functionalCapacity['needs_help_for_tasks'] = [];
        }

        $currentProviders = [];
        if (! self::filterAnswer($report->current_providers)) {
            foreach ($report->current_providers as $provider) {
                $providerData = [
                    'provider_name' => ! self::filterAnswer($provider['provider_name'])
                        ? ucwords(strtolower($provider['provider_name']))
                        : 'N/A',
                    'location'      => ! self::filterAnswer($provider['location'])
                        ? ucwords(strtolower($provider['location']))
                        : 'N/A',
                    'specialty'     => ! self::filterAnswer($provider['specialty'])
                        ? ucwords(strtolower($provider['specialty']))
                        : 'N/A',
                    'phone_number'  => ! self::filterAnswer($provider['phone_number'])
                        ? $provider['phone_number']
                        : 'N/A',
                ];
                $currentProviders[] = $providerData;
            }
        }

        $advancedCarePlanning = [];
        $advancedCarePlanning['living_will'] = strtolower($this->checkInputValueIsNotEmpty($report->advanced_care_planning['living_will'],
            'living_will', []));
        $advancedCarePlanning['has_attorney'] = strtolower($this->checkInputValueIsNotEmpty($report->advanced_care_planning['has_attorney'],
            'has_attonery', [])) === 'yes'
            ? 'has'
            : 'does not have';
        $advancedCarePlanning['existing_copy'] = strtolower(getStringValueFromAnswer($report->advanced_care_planning['existing_copy'])) === 'yes'
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
            'vitals'                     => self::checkEachItemIfNotEmptyOf($vitals, 'vitals_bmi_body_diagnosis_bp'),
            'diet'                       => $diet,
            'social_factors'             => self::validateInputsOfDependentQuestions($report->social_factors,
                'social_factors'),
            'sexual_activity'            => self::validateInputsOfDependentQuestions($report->sexual_activity,
                'sexual_activity'),
            'exercise_activity_levels'   => self::checkInputValueIsNotEmpty($report->exercise_activity_levels,
                'exercise_activity_levels', []),
            'functional_capacity'        => $functionalCapacity,
            'current_providers'          => $currentProviders,
            'advanced_care_planning'     => $advancedCarePlanning,
            'specific_patient_requests'  => getStringValueFromAnswer($report->specific_patient_requests),
        ]);
    }

    /**
     * @param $answer
     * @param string $errorDescription
     * @param $answerToCompare
     *
     * @return string
     * @throws Exception
     */
    public static function checkInputValueIsNotEmpty($answer, string $errorDescription, $answerToCompare)
    {
        $value = ! is_array($answer)
            ? $answer
            : getStringValueFromAnswer($answer);

        return $value;

        /*
         * I think we should not be doing this here
        if ($value === '') { // check if it should be empty
            $allowExceptions = [ //contains all answers(the keys of this arr) that should be empty and each key has extra checks
                'HPV' => [
                    'operator' => 'greater_or_equal_than',
                    'validationValue' => 26,
                    'keyToCheck' => ['age'][0],
                ]
                //@todo: add more here when refactoring: eg Question:breast Cancer, Prostate etc...
            ];

            $keyToCheck = $allowExceptions[$errorDescription]['keyToCheck'];
            $operator = array_key_exists('operator', $allowExceptions[$errorDescription])
                ? $allowExceptions[$errorDescription]['operator']
                : '';
            $validationValue = $allowExceptions[$errorDescription]['validationValue'];

            $allowExceptionExists = array_key_exists($errorDescription, $allowExceptions);

            if ($allowExceptionExists && !empty($operator) && $operator === 'greater_or_equal_than') {
                if (getStringValueFromAnswer($answerToCompare[$keyToCheck]) >= $validationValue) {
                    return $value;
                }
            }

            // Here different implementations can be added
        }


        if ($value !== '') {
            return $value;
        } else {
            return self::throwExceptionEmptyAnswer($errorDescription);
        }
        */
    }

    /**
     * @param string $errorDescription
     *
     * @return string
     * @throws Exception
     */
    public static function throwExceptionEmptyAnswer(string $errorDescription)
    {
        $fileName = self::class;
        throw new Exception("Empty answer in: $errorDescription in $fileName");
    }

    /**
     * @param $string1
     * @param $string2
     *
     * @return bool
     */
    public static function caseInsensitiveComparison($string1, $string2)
    {
        return strcasecmp($string1, $string2) === 0;
    }

    /**
     * @param $arrayOfValues
     * @param $errorMessage
     *
     * @return array
     */
    public static function checkEachItemIfNotEmptyOf($arrayOfValues, $errorMessage)
    {
        return array_filter($arrayOfValues, function ($values) use ($errorMessage) {
            if ($values === 'N/A' || self::filterAnswer($values)) {
                self::throwExceptionEmptyAnswer($errorMessage);
            }

            return $values;
        });
    }

    /**
     * @param $answers
     * @param string $errorMessage
     *
     * @return mixed
     * @throws Exception
     */
    public static function validateInputsOfDependentQuestions(array $answers, string $errorMessage)
    {
        $keys = array_keys($answers);
        $validAnswers = collect($keys)->mapWithKeys(function ($key) use ($answers, $errorMessage) {
            $firstAnswerForEachDependentQuestion = self::resolveOrGetFirstAnswers($answers, $key, $errorMessage);

            return self::analyzeFirstAnswersAndTakeAction($firstAnswerForEachDependentQuestion, $key, $errorMessage);
        });
        self::validateOptionalAnswers($validAnswers, $answers, $errorMessage);

        //if no exception is thrown
        return $answers;
    }

    /**
     * - If (array) && has items (string) && first answer of this group of questions is NO then it will return the
     * answers.
     * - If (array) && has items (string) && first answer is YES then will check each answer if NOT empty and it
     * will return answers.
     * - Else if (array) and has item (array) then return the value of the first item of each item (array).
     *
     * @param $answers
     * @param $key
     * @param $errorMessage
     *
     * @return mixed|string
     * @throws Exception
     */
    public static function resolveOrGetFirstAnswers($answers, $key, $errorMessage)
    {
        $firstElem = reset($answers);
        $firstElemIsString = is_string($firstElem);

        if ($firstElemIsString) {
            if ($firstElem === self::NO) {
                return $answers;
            } elseif ($firstElem === self::YES) {
                if (self::filterAnswer($answers[$key])) {
                    self::throwExceptionEmptyAnswer($errorMessage);
                }

                return $answers;
            }
        }

        // 1. go through array and find entry which looks like a yes or a no
        // NOTE: this code has been refactored, because:
        // - when generating report, answers might come in this order: [ 'alcohol' => [ 'drinks' => 'No', 'amount' => [] ] ]
        // - but when reading from DB, answers might be in a different order, so you have to look for the Yes or No
        //   i.e. Not enough, to just return the first entry ['alcohol' => [ 'amount' => [], 'drinks' => 'No' ] ]

        foreach ($answers[$key] as $entry) {
            if (! is_string($entry)) {
                continue;
            }
            if (in_array($entry, [self::YES, self::NO])) {
                return $entry;
            }
        }

        // 2. otherwise return first entry
        return reset($answers[$key]);
    }

    /**
     *  If first Answer of this group (eg Q11) is YES then it will set Q11a,Q11b etc. to optional = false
     *  Meaning they will be checked.
     *
     * @param $firstAnswerForEachDependentQuestion
     * @param $key
     * @param $errorMessage
     *
     * @return array|string
     * @throws Exception
     */
    public static function analyzeFirstAnswersAndTakeAction($firstAnswerForEachDependentQuestion, $key, $errorMessage)
    {
        if (self::filterAnswer($firstAnswerForEachDependentQuestion)) {
            self::throwExceptionEmptyAnswer($errorMessage);
        }

        return $firstAnswerForEachDependentQuestion !== self::YES
            ? [$key => true]
            : [$key => false];
    }

    /**
     * If first answers of Group Questions is YES then we need to check the related (optional) questions inputs.
     * If first answer is NO then the related questions inputs are expected to be empty, so it wont check them.
     *
     * @param $validAnswers
     * @param $answers
     * @param $errorMessage
     *
     * @return void
     * @throws Exception
     */
    public static function validateOptionalAnswers($validAnswers, $answers, $errorMessage)
    {
        foreach ($validAnswers as $key => $isOptional) {
            if ($isOptional) {
                continue;
            }
            foreach ($answers[$key] as $answer) {
                if ((is_string($answer) && self::filterAnswer($answer)) || (is_array($answer) && self::filterAnswer($answer))) {
                    self::throwExceptionEmptyAnswer($errorMessage);
                }
            }
        }
    }
}
