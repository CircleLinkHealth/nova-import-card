<?php

namespace App\Services;

use App\Answer;
use App\HraQuestionIdentifier;
use App\Survey;
use App\User;
use App\VitalsQuestionIdentifier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GenerateProviderReportService extends GenerateReportService
{
    public function __construct(User $patient)
    {
        parent::__construct($patient);
    }

    /**
     * @return Model
     * @throws \Exception
     */
    public function generateData()
    {
        $summary = $this->patient->patientAWVSummaries->first();

        if (! $summary) {
            $summary = $this->patient->patientAWVSummaries()->create([
                'month_year'       => Carbon::now()->startOfMonth(),
                'is_initial_visit' => 1,
            ]);
        }

        $reasonForVisit = 'Subsequent';

        if ($summary->is_initial_visit) {
            $reasonForVisit = 'Initial';
        }

        return $this->patient->providerReports()->updateOrCreate(
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
    }

    private function getDemographicData()
    {
        $demographicData = [];

        $demographicData['age'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::AGE);

        $demographicData['gender'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEX);

        $demographicData['race'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RACE);

        $demographicData['ethnicity'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::ETHNICITY);

        $demographicData['health'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RATE_HEALTH);

        return $demographicData;
    }

    private function getAllergyHistory()
    {
        return $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::ALLERGIES);
    }

    private function getMedicalHistory()
    {
        $medicalHistory = [];

        $medicalHistory['conditions'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::CONDITIONS);

        $medicalHistory['other_conditions'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::CONDITIONS_EXTRA);

        return $medicalHistory;
    }

    private function getMedicationHistory()
    {
        return $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::MEDICATION);
    }

    private function getFamilyMedicalHistory()
    {
        return $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::CONDITIONS_FAMILY_WHO);
    }

    private function getImmunizationHistory()
    {
        $immunizationHistory = [];

        $immunizationHistory['Influenza'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FLU_SHOT);
        $immunizationHistory['Diphtheria/Tetanus'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TETANUS_VACCINATION);
        $immunizationHistory['TDaP Booster'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TDAP_VACCINATION);
        $immunizationHistory['Varicella'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::VARICELLA_VACCINATION);
        $immunizationHistory['Hepatitis B'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::HEPATITIS_B_VACCINATION);
        $immunizationHistory['MMR'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::MEASLES_VACCINATION);
        $immunizationHistory['HPV'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PAPILLOMAVIRUS_VACCINATION);
        $immunizationHistory['Shingles (herpes zoster)'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RZV_ZVL);
        $immunizationHistory['Pneumococcal'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PCV13_PPSV23);

        return $immunizationHistory;
    }

    private function getScreenings()
    {
        $screenings = [];

        $screenings['breast_cancer'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::MAMMOGRAM);
        $screenings['cervical_cancer'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PAP_SMEAR);
        $screenings['colorectal_cancer'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::COLORECTAR_CANCER);
        $screenings['skin_cancer'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SKIN_CANCER);
        $screenings['prostate_cancer'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PROSTATE_CANCER);
        $screenings['glaucoma'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::GLAUCOMA);
        $screenings['osteoporosis'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::OSTEOPOROSIS);
        $screenings['violence'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::INTIMATE_PARTNER_VIOLENCE);

        return $screenings;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getMentalState()
    {
        $answer1 = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::INTEREST_DOING_THINGS);
        $answer2 = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::DEPRESSED);

        $depressionScoresArray = ProviderReportService::depressionScoreArray();

        return [
            'depression_score' => $depressionScoresArray[strtolower(ProviderReportService::checkInputValueIsNotEmpty($answer1,
                    '22.1',
                    []))] + $depressionScoresArray[strtolower(ProviderReportService::checkInputValueIsNotEmpty($answer2,
                    '22.2', []))],
        ];
    }

    private function getVitals()
    {
        $vitals = [];

        $vitals['blood_pressure'] = $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::BLOOD_PRESSURE);

        $vitals['weight'] = $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::WEIGHT);

        $vitals['height'] = $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::HEIGHT);

        $vitals['bmi'] = $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::BMI);

        return $vitals;
    }

    private function getDiet()
    {
        $diet = [];

        $diet['fruits_vegetables'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FRUIT);

        $diet['grain_fiber'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FIBER);

        $diet['fried_fatty'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FATTY_FOOD);

        $diet['sugary_beverages'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SUGAR);

        $diet['change_in_diet'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::APPETITE);

        return $diet;
    }

    private function getSocialFactors()
    {
        $socialFactors = [];

        $socialFactors['tobacco']['has_used'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TOBACCO);
        $socialFactors['tobacco']['last_smoked'] = strtolower($this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TOBACCO_LAST_TIME, ''));
        $socialFactors['tobacco']['amount'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TOBACCO_PACKS);
        $socialFactors['tobacco']['interest_in_quitting'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TOBACCO_QUIT);

        $socialFactors['alcohol']['drinks'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::ALCOHOL);
        $socialFactors['alcohol']['amount'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::ALCOHOL_CONSUMPTION);

        $socialFactors['recreational_drugs']['has_used'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RECREATIONAL_DRUGS);
        $socialFactors['recreational_drugs']['type_of_drug'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RECREATIONAL_DRUGS_WHICH);

        return $socialFactors;
    }

    private function getSexualActivity()
    {
        $sexualActivity = [];

        $sexualActivity['active'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEXUALLY_ACTIVE);
        $sexualActivity['multiple_partners'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEXUALLY_ACTIVE_PARTNERS);
        $sexualActivity['safe_sex'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEXUALLY_ACTIVE_SAFE);

        return $sexualActivity;
    }

    private function getExerciseActivityLevels()
    {
        $exerciseActivityLevels = [];
        $val = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::EXERCISE);
        $exerciseActivityLevels['value'] = lcfirst($val);

        return $exerciseActivityLevels;
    }

    private function getFunctionalCapacity()
    {
        $functionalCapacity = [];

        $functionalCapacity['needs_help_for_tasks'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::DIFFICULTIES);
        $functionalCapacity['have_assistance'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::DIFFICULTIES_ASSISTANCE);

        $functionalCapacity['mci_cognitive']['word_recall'] = $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::WORD_RECALL);
        $functionalCapacity['mci_cognitive']['clock'] = $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::CLOCK_DRAW);
        $functionalCapacity['mci_cognitive']['total'] = $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::TOTAL_SCORE);

        $functionalCapacity['has_fallen'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FALL_INCIDENT);
        $functionalCapacity['hearing_difficulty'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::HEARING);

        return $functionalCapacity;
    }

    private function getCurrentProviders()
    {
        return $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PHYSICIANS);
    }

    private function getAdvancedCarePlanning()
    {
        $advancedCarePlanning = [];

        $advancedCarePlanning['has_attorney'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::MEDICAL_ATTORNEY);
        $advancedCarePlanning['living_will'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::LIVING_WILL);
        $advancedCarePlanning['existing_copy'] = $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::LIVING_WILL_AT_DOCTOR);

        return $advancedCarePlanning;
    }

    private function getSpecificPatientRequests()
    {
        return $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::COMMENTS);
    }
}
