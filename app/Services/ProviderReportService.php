<?php


namespace App\Services;


use Exception;

class ProviderReportService
{
    const YES = 'Yes';
    const NO = 'No';

    /**
     * @param $report
     * @return \Illuminate\Support\Collection
     * @throws Exception
     */
    public function formatReportDataForView($report)
    {
        $demographicData['age'] = $this->getStringValue($report->demographic_data['age'], 'N/A');
        $demographicData['race'] = ucwords(strtolower($this->checkInputValueIsNotEmptyString($report->demographic_data['race'],
            'race')));
        $demographicData['gender'] = strtolower($this->checkInputValueIsNotEmptyString($report->demographic_data['gender'], 'gender'));
        $demographicData['health'] = strtolower($this->checkInputValueIsNotEmptyString($report->demographic_data['health'], 'health'));

        $allergyHistory = [];
        if (!empty($report->allergy_history)) {
            foreach ($report->allergy_history as $allergy) {
                $allergyHistory[] = strtolower($allergy['name']);
            }
        }

        $medicalHistoryConditions = [];
        if (!empty($report->medical_history['conditions'])) {
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
        if (!empty($report->medical_history['other_conditions'])) {
            foreach ($report->medical_history['other_conditions'] as $condition) {
                if (!empty($condition) && !empty($condition['name'])) {
                    $medicalHistoryOther[] = ucwords(strtolower($condition['name']));
                }
            }
        }

        $medicationHistory = [];
        if (!empty($report->medication_history)) {
            foreach ($report->medication_history as $medication) {
                if (!empty($medication)) {
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
        if (!empty($report->family_medical_history)) {
            foreach ($report->family_medical_history as $condition) {
                if (!empty($condition) && !empty($condition['name'])) {
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
        if (!empty($report->immunization_history)) {
            foreach ($report->immunization_history as $immunization => $answer) {
                $received = $this->checkInputValueIsNotEmptyString($answer, $immunization);
                strtolower($received) === 'yes' ? $immunizationsReceived[] = $immunization : $immunizationsNotReceived[] = $immunization;
            }
        }

        $screenings = [];
        if (!empty($report->screenings)) {

            $breastCancer = self::checkInputValueIsNotEmptyString($report->screenings['breast_cancer'], 'breast_cancer');
            if ($breastCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Breast cancer'] = " (Mammogram): Had " . $breastCancer . '.';
            }

            $cervicalCancer = self::checkInputValueIsNotEmptyString($report->screenings['cervical_cancer'], 'cervical_cancer');
            if ($cervicalCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Cervical cancer'] = " (Pap smear): Had " . $cervicalCancer . '.';
            }

            $colorectalCancer = self::checkInputValueIsNotEmptyString($report->screenings['colorectal_cancer'], 'colorectal_cancer');
            if ($colorectalCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Colorectal cancer'] = " (e.g. Fecal Occult Blood Test (FOBT), Fecal Immunohistochemistry Test (FIT) Sigmoidoscopy, Colonoscopy): Had " . $colorectalCancer . '.';
            }

            $skinCancer = self::checkInputValueIsNotEmptyString($report->screenings['skin_cancer'], 'skin_cancer');
            if ($skinCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Skin cancer'] = ": Had " . $skinCancer . '.';
            }

            $prostateCancer = self::checkInputValueIsNotEmptyString($report->screenings['prostate_cancer'], 'prostate_cancer');
            if ($prostateCancer !== '10+ years ago/Never/Unsure') {
                $screenings['Prostate cancer'] = " (Prostate screening Test): Had " . $prostateCancer . '.';
            }

            $glaucoma = self::checkInputValueIsNotEmptyString($report->screenings['glaucoma'], 'glaucoma');
            if ($glaucoma !== '10+ years ago/Never/Unsure') {
                $screenings['Glaucoma'] = ": Had " . $glaucoma . '.';
            }

            $osteoporosis = self::checkInputValueIsNotEmptyString($report->screenings['osteoporosis'], 'osteoporosis');
            if ($osteoporosis !== '10+ years ago/Never/Unsure') {
                $screenings['Osteoporosis'] = " (Bone Density Test): Had " . $osteoporosis . '.';
            }

            $violence = self::checkInputValueIsNotEmptyString($report->screenings['violence'], 'violence');
            if ($violence !== '10+ years ago/Never/Unsure') {
                $screenings['Intimate Partner Violence/Domestic Violence'] = ": Had " . $violence . '.';
            }
        }

        $mentalState = [];
        if (!empty($report->mental_state['depression_score']) || $report->mental_state['depression_score'] === 0) {
            $mentalState['score'] = $report->mental_state['depression_score'];
            $diagnosis = 'no depression';
            if ($report->mental_state['depression_score'] > 2) {
                $diagnosis = 'potential depression - further testing may be required';
            }
            $mentalState['diagnosis'] = $diagnosis;
        }

        $vitals = [];

        if (!empty($report->vitals['blood_pressure'])) {
            $vitals['blood_pressure']['first_metric'] = (int)$report->vitals['blood_pressure']['first_metric'] !== 0
                ? $report->vitals['blood_pressure']['first_metric']
                : 'N/A';
            $vitals['blood_pressure']['second_metric'] = (int)$report->vitals['blood_pressure']['second_metric'] !== 0
                ? $report->vitals['blood_pressure']['second_metric']
                : 'N/A';
        }
        if (!empty($report->vitals['bmi'])) {

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
        if (!empty($report->diet)) {
            $diet['fried_fatty'] = $this->checkInputValueIsNotEmptyString($report->diet['fried_fatty'], 'fried_fatty');
            $diet['grain_fiber'] = $this->checkInputValueIsNotEmptyString($report->diet['grain_fiber'], 'grain_fiber');
            $diet['sugary_beverages'] = $this->checkInputValueIsNotEmptyString($report->diet['sugary_beverages'], 'sugary_beverages');
            $diet['fruits_vegetables'] = $this->checkInputValueIsNotEmptyString($report->diet['fruits_vegetables'], 'fruits_vegetables');
            $changeInDiet = self::checkInputValueIsNotEmptyString($report->diet['change_in_diet'], 'change_in_diet');
            $change = $changeInDiet;

            $diet['have_changed_diet'] = strtolower($change) === 'yes'
                ? 'have'
                : 'have not';

        }

        $functionalCapacity = [];
        $functionalCapacity['has_fallen'] = strtolower($this->checkInputValueIsNotEmptyString($report->functional_capacity['has_fallen'], 'has_fallen')) === 'yes'
            ? 'has'
            : 'has not';
        $functionalCapacity['have_assistance'] = strtolower($this->getStringValue($report->functional_capacity['have_assistance'])) === 'yes'
            ? 'do'
            : 'do not';
        $functionalCapacity['hearing_difficulty'] = strtolower($this->checkInputValueIsNotEmptyString($report->functional_capacity['hearing_difficulty'], 'hearing_difficulty')) === 'yes'
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
        if (!empty($report->functional_capacity['needs_help_for_tasks'])) {
            foreach ($report->functional_capacity['needs_help_for_tasks'] as $task) {
                $functionalCapacity['needs_help_for_tasks'][] = strtolower($task['name']);
            }
        } else {
            $functionalCapacity['needs_help_for_tasks'] = [];
        }

        $currentProviders = [];
        if (!empty($report->current_providers)) {
            foreach ($report->current_providers as $provider) {
                $providerData = [
                    'provider_name' => !empty($provider['provider_name'])
                        ? ucwords(strtolower($provider['provider_name']))
                        : 'N/A',
                    'location' => !empty($provider['location'])
                        ? ucwords(strtolower($provider['location']))
                        : 'N/A',
                    'specialty' => !empty($provider['specialty'])
                        ? ucwords(strtolower($provider['specialty']))
                        : 'N/A',
                    'phone_number' => !empty($provider['phone_number'])
                        ? $provider['phone_number']
                        : 'N/A',
                ];
                $currentProviders[] = $providerData;
            }
        }

        $advancedCarePlanning = [];
        $advancedCarePlanning['living_will'] = strtolower($this->checkInputValueIsNotEmptyString($report->advanced_care_planning['living_will'], 'living_will'));
        $advancedCarePlanning['has_attorney'] = strtolower($this->checkInputValueIsNotEmptyString($report->advanced_care_planning['has_attorney'], 'has_attonery')) === 'yes'
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
            'vitals' => $vitals,
            'diet' => $diet,
            'social_factors' => self::validateInputsOfDependentQuestions($report->social_factors, 'social_factors'),
            'sexual_activity' => self::validateInputsOfDependentQuestions($report->sexual_activity, 'sexual_activity'),
            'exercise_activity_levels' => self::checkInputValueIsNotEmptyString($report->exercise_activity_levels, 'exercise_activity_levels'),
            'functional_capacity' => $functionalCapacity,
            'current_providers' => $currentProviders,
            'advanced_care_planning' => $advancedCarePlanning,
            'specific_patient_requests' => $this->getStringValue($report->specific_patient_requests),
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

    /**
     * @param $answer
     * @param $errorDescription
     * @return string
     * @throws Exception
     */
    public static function checkInputValueIsNotEmptyString($answer, string $errorDescription)
    {
        $value = !is_array($answer) ? $answer : self::getStringValue($answer);

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
            if (empty($answers[$key])) {
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
        if (empty($firstAnswerForEachDependentQuestion)) {
            self::throwExceptionEmptyAnswer($errorMessage);
        }
        return $firstAnswerForEachDependentQuestion !== self::YES ? [$key => true] : [$key => false];
    }

    /**
     * If first answers of Group Questions is YES then we need to check the related (optional) questions inputs.
     * If first answer is NO then the related questions inputs are expected to be null, so it wont check them.
     *
     * @param $validAnswers
     * @param $answers
     * @param $errorMessage
     * @return bool
     * @throws Exception
     */
    public static function validateOptionalAnswers($validAnswers, $answers, $errorMessage)
    {
        foreach ($validAnswers as $key => $isOptional) {
            if ($isOptional) {
                continue;
            }
            foreach ($answers[$key] as $answer) {
                if (is_string($answer) && empty($answer)) {
                    self::throwExceptionEmptyAnswer($errorMessage);
                }
                if (is_array($answer) && !empty(self::checkInputValueIsNotEmptyArray($answer, $errorMessage))) {
                    continue;
                }
            }
        }
        return true;
    }

    /**
     * @param $answers
     * @param $errorDescription
     * @return mixed
     * @throws Exception
     */
    public static function checkInputValueIsNotEmptyArray($answers, $errorDescription)
    {
        if (!empty($answers)) {
            return $answers;
        } else {
            return self::throwExceptionEmptyAnswer($errorDescription);
        }

    }
}
