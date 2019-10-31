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
            'not at all' => 0,
            'several days' => 1,
            'more than half the days' => 2,
            'nearly every day' => 3,
        ];
    }

//@todo: At some point move all helper functions to a different file

    /**
     * @param $report
     * @return \Illuminate\Support\Collection
     * @throws Exception
     */
    public function formatReportDataForView($report)
    {
        $demographicData['age'] = $this->getStringValue($report->demographic_data['age'], 'N/A');
        $demographicData['race'] = ucwords(strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['race'],
            'race', [])));
        $demographicData['ethnicity'] = ucwords(strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['ethnicity'],
            'ethnicity', [])));
        $demographicData['gender'] = strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['gender'], 'gender', []));
        $demographicData['health'] = strtolower($this->checkInputValueIsNotEmpty($report->demographic_data['health'], 'health', []));

        $allergyHistory = [];
        if (!self::filterAnswer($report->allergy_history)) {
            foreach ($report->allergy_history as $allergy) {
                $allergyHistory[] = strtolower($allergy['name']);
            }
        }

        $medicalHistoryConditions = [];
        if (!self::filterAnswer($report->medical_history['conditions'])) {
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
        if (!self::filterAnswer($report->medical_history['other_conditions'])) {
            foreach ($report->medical_history['other_conditions'] as $condition) {
                if (!self::filterAnswer($condition) && !self::filterAnswer($condition['name'])) {
                    $medicalHistoryOther[] = ucwords(strtolower($condition['name']));
                }
            }
        }

        $medicationHistory = [];
        if (!self::filterAnswer($report->medication_history)) {
            foreach ($report->medication_history as $medication) {
                if (!self::filterAnswer($medication)) {
                    $medicationData = [
                        'dose' => $medication['dose'],
                        'drug' => ucwords(strtolower($medication['drug'])),
                        'frequency' => strtolower($medication['frequency']),
                    ];

                    $medicationHistory[] = $medicationData;
                }
            }
        }

        $familyConditions = [];
        if (!self::filterAnswer($report->family_medical_history)) {
            foreach ($report->family_medical_history as $condition) {
                if (!self::filterAnswer($condition) && !self::filterAnswer($condition['name'])) {
                    $conditionData = [
                        'name' => ucwords(strtolower($condition['name'])),
                        'family' => strtolower(implode(', ', $condition['family'])),
                    ];
                    $familyConditions[] = $conditionData;
                }
            }
        }

        $immunizationsReceived = [];
        $immunizationsNotReceived = [];
        if (!self::filterAnswer($report->immunization_history)) {
            foreach ($report->immunization_history as $immunization => $answer) {
                $received = $this->checkInputValueIsNotEmpty($answer, $immunization, $report->demographic_data);
                strtolower($received) === 'yes' ? $immunizationsReceived[] = $immunization : $immunizationsNotReceived[] = $immunization;
            }
        }

        $screenings = [];
        if (!self::filterAnswer($report->screenings)) {

            $breastCancer = $this->getStringValue($report->screenings['breast_cancer']);
            if ($breastCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Breast cancer'] = " (Mammogram): Had " . $breastCancer . '.';
            }

            $cervicalCancer = $this->getStringValue($report->screenings['cervical_cancer']);
            if ($cervicalCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Cervical cancer'] = " (Pap smear): Had " . $cervicalCancer . '.';
            }

            $colorectalCancer = self::checkInputValueIsNotEmpty($report->screenings['colorectal_cancer'], 'colorectal_cancer', []);
            if ($colorectalCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Colorectal cancer'] = " (e.g. Fecal Occult Blood Test (FOBT), Fecal Immunohistochemistry Test (FIT) Sigmoidoscopy, Colonoscopy): Had " . $colorectalCancer . '.';
            }

            $skinCancer = self::checkInputValueIsNotEmpty($report->screenings['skin_cancer'], 'skin_cancer', []);
            if ($skinCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Skin cancer'] = ": Had " . $skinCancer . '.';
            }

            $prostateCancer = $this->getStringValue($report->screenings['prostate_cancer']);
            if ($prostateCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Prostate cancer'] = " (Prostate screening Test): Had " . $prostateCancer . '.';
            }

            $glaucoma = self::checkInputValueIsNotEmpty($report->screenings['glaucoma'], 'glaucoma', []);
            if ($glaucoma !== '10+ years ago/Never/Unsure') {
                $screenings['Glaucoma'] = ": Had " . $glaucoma . '.';
            }

            $osteoporosis = self::checkInputValueIsNotEmpty($report->screenings['osteoporosis'], 'osteoporosis', []);
            if ($osteoporosis !== '10+ years ago/Never/Unsure') {
                $screenings['Osteoporosis'] = " (Bone Density Test): Had " . $osteoporosis . '.';
            }

            $violence = $this->getStringValue($report->screenings['violence']);
            if ($violence !== '10+ years ago/Never/Unsure') {
                $screenings['Intimate Partner Violence/Domestic Violence'] = ": Had " . $violence . '.';
            }
        }

        $mentalState = [];
        if (!self::filterAnswer($report->mental_state['depression_score']) || $report->mental_state['depression_score'] === 0) {
            $mentalState['score'] = $report->mental_state['depression_score'];
            $diagnosis = 'no depression';
            if ($report->mental_state['depression_score'] > 2) {
                $diagnosis = 'potential depression - further testing may be required';
            }
            $mentalState['diagnosis'] = $diagnosis;
        }

        $vitals = [];

        if (!self::filterAnswer($report->vitals['blood_pressure'])) {
            $vitals['blood_pressure']['first_metric'] = (int)$report->vitals['blood_pressure']['first_metric'] !== 0
                ? $report->vitals['blood_pressure']['first_metric']
                : 'N/A';
            $vitals['blood_pressure']['second_metric'] = (int)$report->vitals['blood_pressure']['second_metric'] !== 0
                ? $report->vitals['blood_pressure']['second_metric']
                : 'N/A';
        }
        if (!self::filterAnswer($report->vitals['bmi'])) {

            $bmiVal = $this->getStringValue($report->vitals['bmi']);

            $vitals['bmi'] = $bmiVal;
            if ((float)$bmiVal < 18.5) {
                $vitals['bmi_diagnosis'] = 'low';
                $vitals['body_diagnosis'] = 'underweight';
            } elseif ((float)$bmiVal > 25) {
                $vitals['bmi_diagnosis'] = 'high';
                $vitals['body_diagnosis'] = (float)$bmiVal < 30
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


        $vitals['height']['feet'] = (int)$report->vitals['height']['feet'] !== 0
            ? $report->vitals['height']['feet']
            : 'N/A';
        $vitals['height']['inches'] = (int)$report->vitals['height']['inches'] !== 0
            ? $report->vitals['height']['inches']
            : 'N/A';


        $vitals['weight'] = $this->getStringValue($report->vitals['weight'], 'N/A');


        $diet = [];
        if (!self::filterAnswer($report->diet)) {
            $diet['fried_fatty'] = $this->checkInputValueIsNotEmpty($report->diet['fried_fatty'], 'fried_fatty', []);
            $diet['grain_fiber'] = $this->checkInputValueIsNotEmpty($report->diet['grain_fiber'], 'grain_fiber', []);
            $diet['sugary_beverages'] = $this->checkInputValueIsNotEmpty($report->diet['sugary_beverages'], 'sugary_beverages', []);
            $diet['fruits_vegetables'] = $this->checkInputValueIsNotEmpty($report->diet['fruits_vegetables'], 'fruits_vegetables', []);
            $changeInDiet = self::checkInputValueIsNotEmpty($report->diet['change_in_diet'], 'change_in_diet', []);
            $change = $changeInDiet;

            $diet['have_changed_diet'] = strtolower($change) === 'yes'
                ? 'have'
                : 'have not';

        }

        $functionalCapacity = [];
        $functionalCapacity['has_fallen'] = strtolower($this->checkInputValueIsNotEmpty($report->functional_capacity['has_fallen'], 'has_fallen', [])) === 'yes'
            ? 'has'
            : 'has not';
        $functionalCapacity['have_assistance'] = strtolower($this->getStringValue($report->functional_capacity['have_assistance'])) === 'yes'
            ? 'do'
            : 'do not';
        $functionalCapacity['hearing_difficulty'] = strtolower($this->checkInputValueIsNotEmpty($report->functional_capacity['hearing_difficulty'], 'hearing_difficulty', [])) === 'yes'
            ? 'has'
            : (strtolower($this->getStringValue($report->functional_capacity['hearing_difficulty'])) === 'sometimes'
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
        if (!self::filterAnswer($report->functional_capacity['needs_help_for_tasks'])) {
            foreach ($report->functional_capacity['needs_help_for_tasks'] as $task) {
                $functionalCapacity['needs_help_for_tasks'][] = strtolower($task['name']);
            }
        } else {
            $functionalCapacity['needs_help_for_tasks'] = [];
        }

        $currentProviders = [];
        if (!self::filterAnswer($report->current_providers)) {
            foreach ($report->current_providers as $provider) {
                $providerData = [
                    'provider_name' => !self::filterAnswer($provider['provider_name'])
                        ? ucwords(strtolower($provider['provider_name']))
                        : 'N/A',
                    'location' => !self::filterAnswer($provider['location'])
                        ? ucwords(strtolower($provider['location']))
                        : 'N/A',
                    'specialty' => !self::filterAnswer($provider['specialty'])
                        ? ucwords(strtolower($provider['specialty']))
                        : 'N/A',
                    'phone_number' => !self::filterAnswer($provider['phone_number'])
                        ? $provider['phone_number']
                        : 'N/A',
                ];
                $currentProviders[] = $providerData;
            }
        }

        $advancedCarePlanning = [];
        $advancedCarePlanning['living_will'] = strtolower($this->checkInputValueIsNotEmpty($report->advanced_care_planning['living_will'], 'living_will', []));
        $advancedCarePlanning['has_attorney'] = strtolower($this->checkInputValueIsNotEmpty($report->advanced_care_planning['has_attorney'], 'has_attonery', [])) === 'yes'
            ? 'has'
            : 'does not have';
        $advancedCarePlanning['existing_copy'] = strtolower($this->getStringValue($report->advanced_care_planning['existing_copy'])) === 'yes'
            ? 'is'
            : 'is not';


        return collect([
            'reason_for_visit' => $report->reason_for_visit,
            'demographic_data' => $demographicData,
            'allergy_history' => $allergyHistory,
            'medical_history' => $medicalHistoryConditions,
            'medical_history_other' => $medicalHistoryOther,
            'medication_history' => $medicationHistory,
            'family_medical_history' => $familyConditions,
            'immunizations_received' => $immunizationsReceived,
            'immunizations_not_received' => $immunizationsNotReceived,
            'screenings' => $screenings,
            'mental_state' => $mentalState,
            'vitals' => self::checkEachItemIfNotEmptyOf($vitals, 'vitals_bmi_body_diagnosis_bp'),
            'diet' => $diet,
            'social_factors' => self::validateInputsOfDependentQuestions($report->social_factors, 'social_factors'),
            'sexual_activity' => self::validateInputsOfDependentQuestions($report->sexual_activity, 'sexual_activity'),
            'exercise_activity_levels' => self::checkInputValueIsNotEmpty($report->exercise_activity_levels, 'exercise_activity_levels', []),
            'functional_capacity' => $functionalCapacity,
            'current_providers' => $currentProviders,
            'advanced_care_planning' => $advancedCarePlanning,
            'specific_patient_requests' => $this->getStringValue($report->specific_patient_requests),
        ]);
    }

    public static function getStringValue($val, $default = '')
    {

        if (self::filterAnswer($val)) {
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

    /**
     * if the result of this function === true THEN will throw exception from: validateOptionalAnswers()
     *
     * @param $var
     * @return bool
     */
    public static function filterAnswer($var)
    {
        return ($var === '' || $var === [] || $var === null);
    }

    /**
     * @param $answer
     * @param string $errorDescription
     * @param $answerToCompare
     * @return string
     * @throws Exception
     */
    public static function checkInputValueIsNotEmpty($answer, string $errorDescription, $answerToCompare)
    {
        $value = !is_array($answer) ? $answer : self::getStringValue($answer);

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
            $operator = array_key_exists('operator', $allowExceptions[$errorDescription]) ? $allowExceptions[$errorDescription]['operator'] : '';
            $validationValue = $allowExceptions[$errorDescription]['validationValue'];

            $allowExceptionExists = array_key_exists($errorDescription, $allowExceptions);

            if ($allowExceptionExists && !empty($operator) && $operator === 'greater_or_equal_than') {
                if (self::getStringValue($answerToCompare[$keyToCheck]) >= $validationValue) {
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
    }

    /**
     * @param string $errorDescription
     * @return string
     * @throws Exception
     */
    public static function throwExceptionEmptyAnswer(string $errorDescription)
    {
        $fileName = ProviderReportService::class;
        throw new Exception("Empty answer in: $errorDescription in $fileName");
    }

    /**
     * @param $arrayOfValues
     * @param $errorMessage
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
//if no exception is catched
        return $answers;
    }

    /**
     * If (array) && has items (string) && first answer of this group of questions is NO then it will return the answers.
     * If (array) && has items (string) && first answer is YES then will check each answer if NOT empty and it will return answers.
     * Else if (array) and has item (array) then return the value of the first item of each item (array)
     *
     * @param $answers
     * @param $key
     * @param $errorMessage
     * @return mixed|string
     * @throws Exception
     */
    public static function resolveOrGetFirstAnswers($answers, $key, $errorMessage)
    {
        $firstElem = reset($answers);
        $firstElemIsString = is_string($firstElem);
        if ($firstElemIsString && $firstElem === self::NO) {
            return $answers;
        } elseIf ($firstElemIsString && $firstElem === self::YES) {
            if (self::filterAnswer($answers[$key])) {
                self::throwExceptionEmptyAnswer($errorMessage);
            }
            return $answers;
        }
        return reset($answers[$key]);
    }

    /**
     *
     *  If first Answer of this group (eg Q11) is YES then it will set Q11a,Q11b etc. to optional = false
     *  Meaning they will be checked.
     *
     * @param $firstAnswerForEachDependentQuestion
     * @param $key
     * @param $errorMessage
     * @return array|string
     * @throws Exception
     */
    public static function analyzeFirstAnswersAndTakeAction($firstAnswerForEachDependentQuestion, $key, $errorMessage)
    {
        if (self::filterAnswer($firstAnswerForEachDependentQuestion)) {
            self::throwExceptionEmptyAnswer($errorMessage);
        }
        return $firstAnswerForEachDependentQuestion !== self::YES ? [$key => true] : [$key => false];
    }

    /**
     * If first answers of Group Questions is YES then we need to check the related (optional) questions inputs.
     * If first answer is NO then the related questions inputs are expected to be empty, so it wont check them.
     *
     * @param $validAnswers
     * @param $answers
     * @param $errorMessage
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
