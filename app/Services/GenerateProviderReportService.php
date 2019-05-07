<?php


namespace App\Services;


use App\Survey;
use App\User;
use Carbon\Carbon;

class GenerateProviderReportService
{
    protected $patient;

    protected $date;

    protected $hraInstance;

    protected $vitalsInstance;

    protected $hraAnswers;

    protected $vitalsAnswers;

    protected $hraQuestions;

    protected $vitalsQuestions;


    public function __construct(User $patient, $date)
    {
        //patient contains survey data and existing provider reports
        $this->patient = $patient;
        $this->date    = Carbon::parse($date);

        $this->hraInstance    = $this->patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance = $this->patient->surveyInstances->where('survey.name', Survey::VITALS)->first();

        $this->hraQuestions    = $this->hraInstance->questions;
        $this->vitalsQuestions = $this->vitalsInstance->questions;

        $this->hraAnswers    = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);

    }

    //make possible to edit data on existing report
    public function generateData()
    {
        //how to calculate if the visit was initial or subsequent? One thought would be that to check if there is an existing report like below. However is it probable that the patient will
        //edit their surveys after they have completed both during the same visit?
        $reasonForVisit = 'Initial';

        if ($this->patient->providerReports->isNotEmpty()) {
            $reasonForVisit = 'Subsequent';
        }

        $report = $this->patient->providerReports()->updateOrCreate(
            [
                'hra_instance_id'    => $this->hraInstance->id,
                'vitals_instance_id' => $this->vitalsInstance->id,
            ], [
                'reason_for_visit'          => $reasonForVisit,
                'demographic_data'          => $this->getDemographicData(),
                'allergy_history'           => $this->getAllergyHistory(),
                'medical_history'           => $this->getMedicalHistory(),
                'medication_history'        => $this->getMedicationHistory(),
                'family_medical_history'    => $this->getFamilyMedicalHistory(),
                'immunization_history'      => $this->getImmunizationHistory(),
                'screenings'                => $this->getScreenings(),
                'mental_state'              => $this->getMentalState(),
                'vitals'                    => $this->getVitals(),
                'diet'                      => $this->getDiet(),
                'social_factors'            => $this->getSocialFactors(),
                'sexual_activity'           => $this->getSexualActivity(),
                'exercise_activity_levels'  => $this->getExerciseActivityLevels(),
                'functional_capacity'       => $this->getFunctionalCapacity(),
                'current_providers'         => $this->getCurrentProviders(),
                'advanced_care_planning'    => $this->getAdvancedCarePlanning(),
                'specific_patient_requests' => $this->getSpecificPatientRequests(),
            ]
        );

        return $report;
    }


    private function getDemographicData()
    {
        $demographicData = [];

        $demographicData['age'] = $this->answerForHraQuestionWithOrder(2);

        $demographicData['gender'] = $this->answerForHraQuestionWithOrder(4);

        $demographicData['race'] = $this->answerForHraQuestionWithOrder(1);

        $demographicData['health'] = $this->answerForHraQuestionWithOrder(5);

        return $demographicData;

    }

    private function getAllergyHistory()
    {
        return $this->answerForHraQuestionWithOrder(21);
    }

    private function getMedicalHistory()
    {

        $medicalHistory = [];

        $medicalHistory['conditions'] = $this->answerForHraQuestionWithOrder(16);

        $medicalHistory['other_conditions'] = $this->answerForHraQuestionWithOrder(17);

        return $medicalHistory;
    }

    private function getMedicationHistory()
    {
        return$this->answerForHraQuestionWithOrder(20);
    }

    private function getFamilyMedicalHistory()
    {
        return  $this->answerForHraQuestionWithOrder(18, 'a');
    }

    private function getImmunizationHistory()
    {

        $immunizationHistory = [];

        $immunizationHistory['Influenza']                = $this->answerForHraQuestionWithOrder(26);
        $immunizationHistory['Diphtheria/Tetanus']       = $this->answerForHraQuestionWithOrder(27);
        $immunizationHistory['TDaP Booster']             = $this->answerForHraQuestionWithOrder(28);
        $immunizationHistory['Varicella']                = $this->answerForHraQuestionWithOrder(29);
        $immunizationHistory['Hepatitis B']              = $this->answerForHraQuestionWithOrder(30);
        $immunizationHistory['MMR']                      = $this->answerForHraQuestionWithOrder(31);
        $immunizationHistory['HPV']                      = $this->answerForHraQuestionWithOrder(32);
        $immunizationHistory['Shingles (herpes zoster)'] = $this->answerForHraQuestionWithOrder(33);
        $immunizationHistory['Pneumococcal']             = $this->answerForHraQuestionWithOrder(34);

        return $immunizationHistory;
    }

    private function getScreenings()
    {
        $screenings = [];

        $screenings['breast_cancer']     = $this->answerForHraQuestionWithOrder(35);
        $screenings['cervical_cancer']   = $this->answerForHraQuestionWithOrder(36);
        $screenings['colorectal_cancer'] = $this->answerForHraQuestionWithOrder(37);
        $screenings['skin_cancer']       = $this->answerForHraQuestionWithOrder(38);
        $screenings['prostate_cancer']   = $this->answerForHraQuestionWithOrder(39);
        $screenings['glaucoma']          = $this->answerForHraQuestionWithOrder(40);
        $screenings['osteoporosis']      = $this->answerForHraQuestionWithOrder(41);
        $screenings['violence']          = $this->answerForHraQuestionWithOrder(42);

        return $screenings;
    }

    private function getMentalState()
    {
        //see values for phq2
        $phq2scores = [
            'not at all'              => 0,
            'several days'            => 1,
            'more than half the days' => 2,
            'nearly every day'        => 3,
        ];

        $answer1 = $this->answerForHraQuestionWithOrder(22, '1');
        $answer2 = $this->answerForHraQuestionWithOrder(22, '2');

        return [
            'depression_score' => $phq2scores[strtolower($answer1)] + $phq2scores[strtolower($answer2)],
        ];
    }

    private function getVitals()
    {

        $vitals = [];

        $vitals['blood_pressure'] = $this->answerForVitalsQuestionWithOrder(1);

        $vitals['weight'] = $this->answerForVitalsQuestionWithOrder(2);

        $vitals['height'] = $this->answerForVitalsQuestionWithOrder(3);

        $vitals['bmi'] = $this->answerForVitalsQuestionWithOrder(4);

        return $vitals;

    }

    private function getDiet()
    {

        $diet = [];

        $diet['fruits_vegetables'] = $this->answerForHraQuestionWithOrder(6);

        $diet['grain_fiber'] = $this->answerForHraQuestionWithOrder(7);

        $diet['fried_fatty'] = $this->answerForHraQuestionWithOrder(8);

        $diet['sugary_beverages'] = $this->answerForHraQuestionWithOrder(9);

        $diet['change_in_diet'] = $this->answerForHraQuestionWithOrder(10);

        return $diet;
    }

    private function getSocialFactors()
    {
        $socialFactors = [];

        $socialFactors['tobacco']['has_used']             = $this->answerForHraQuestionWithOrder(11);
        $socialFactors['tobacco']['last_smoked']          = $this->answerForHraQuestionWithOrder(11, 'b');
        $socialFactors['tobacco']['amount']               = $this->answerForHraQuestionWithOrder(11, 'c');
        $socialFactors['tobacco']['interest_in_quitting'] = $this->answerForHraQuestionWithOrder(11, 'd');

        $socialFactors['alcohol']['drinks'] = $this->answerForHraQuestionWithOrder(12);
        $socialFactors['alcohol']['amount'] = $this->answerForHraQuestionWithOrder(12, 'a');

        $socialFactors['recreational_drugs']['has_used']     = $this->answerForHraQuestionWithOrder(13);
        $socialFactors['recreational_drugs']['type_of_drug'] = $this->answerForHraQuestionWithOrder(13, 'a');

        return $socialFactors;

    }

    private function getSexualActivity()
    {
        $sexualActivity = [];

        $sexualActivity['active']            = $this->answerForHraQuestionWithOrder(15);
        $sexualActivity['multiple_partners'] = $this->answerForHraQuestionWithOrder(15, 'a');
        $sexualActivity['safe_sex']          = $this->answerForHraQuestionWithOrder(15, 'b');

        return $sexualActivity;
    }

    private function getExerciseActivityLevels()
    {
        return $this->answerForHraQuestionWithOrder(14);
    }

    private function getFunctionalCapacity()
    {
        $functionalCapacity = [];

        $functionalCapacity['needs_help_for_tasks'] = $this->answerForHraQuestionWithOrder(23);
        $functionalCapacity['have_assistance']      = $this->answerForHraQuestionWithOrder(23, 'a');

        $functionalCapacity['mci_cognitive']['word_recall'] = $this->answerForVitalsQuestionWithOrder(5, 'a');
        $functionalCapacity['mci_cognitive']['clock']       = $this->answerForVitalsQuestionWithOrder(5, 'b');
        $functionalCapacity['mci_cognitive']['total']       = $this->answerForVitalsQuestionWithOrder(5, 'c');

        $functionalCapacity['has_fallen']         = $this->answerForHraQuestionWithOrder(24);
        $functionalCapacity['hearing_difficulty'] = $this->answerForHraQuestionWithOrder(25);

        return $functionalCapacity;
    }

    private function getCurrentProviders()
    {
        return $this->answerForHraQuestionWithOrder(43);
    }

    private function getAdvancedCarePlanning()
    {

        $advancedCarePlanning = [];

        $advancedCarePlanning['has_attorney']  = $this->answerForHraQuestionWithOrder(44);
        $advancedCarePlanning['living_will']   = $this->answerForHraQuestionWithOrder(45);
        $advancedCarePlanning['existing_copy'] = $this->answerForHraQuestionWithOrder(45, 'a');

        return $advancedCarePlanning;
    }

    private function getSpecificPatientRequests()
    {
        return $this->answerForHraQuestionWithOrder(46);
    }

    private function answerForHraQuestionWithOrder($order, $subOrder = null)
    {
        $question = $this->hraQuestions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();

        $answer = $this->hraAnswers->where('question_id', $question->id)->first();

        if ( ! $answer) {
            return [];
        }

        return array_key_exists('value', $answer->value) ? $answer->value['value'] : $answer->value;
    }


    private function answerForVitalsQuestionWithOrder($order, $subOrder = null)
    {
        $question = $this->vitalsQuestions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();

        $answer = $this->vitalsAnswers->where('question_id', $question->id)->first();

        if ( ! $answer) {
            return [];
        }

        return array_key_exists('value', $answer->value) ? $answer->value['value'] : $answer->value;

    }
}