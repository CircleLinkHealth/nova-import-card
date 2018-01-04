<?php namespace App\Services;

use App\CareplanAssessment;
use App\Repositories\CareplanRepository;
use App\Repositories\CareplanAssessmentRepository;

class CareplanAssessmentService
{
    private $assessmentRepo;
    private $careplanRepo;

    public function __construct(CareplanAssessmentRepository $assessmentRepo, CareplanRepository $careplanRepo) {
        $this->assessmentRepo = $assessmentRepo;
        $this->careplanRepo = $careplanRepo;
    }

    public function repo() {
        return $this->assessmentRepo;
    }

    function exists($careplanId) {
        return $this->repo()->model()->where([ 'careplan_id' => $careplanId ])->first(['id']) != null;
    }

    public function save(CareplanAssessment $assessment) {
        if ($assessment) {
            if (!$this->exists($assessment->careplan_id)) {
                $assessment->save();
                return $assessment;
            }
            else {
                $savedAssessments = $this->repo()->model()->where([ 'careplan_id' => $assessment->careplan_id ]);
                $savedAssessments->update([
                    'provider_approver_id' => $assessment->provider_approver_id,
                    'alcohol_misuse_counseling' => $assessment->alcohol_misuse_counseling,
                    'diabetes_screening_interval' => $assessment->diabetes_screening_interval,
                    'diabetes_screening_last_date' => $assessment->diabetes_screening_last_date,
                    'diabetes_screening_next_date' => $assessment->diabetes_screening_next_date,
                    'diabetes_screening_risk' => $assessment->diabetes_screening_risk,
                    'eye_screening_last_date' => $assessment->eye_screening_last_date,
                    'eye_screening_next_date' => $assessment->eye_screening_next_date,
                    'key_treatment' => $assessment->key_treatment,
                    'patient_functional_assistance_areas' => $assessment->patient_functional_assistance_areas,
                    'patient_psychosocial_areas_to_watch' => $assessment->patient_psychosocial_areas_to_watch,
                    'risk' => $assessment->risk,
                    'risk_factors' => $assessment->risk_factors,
                    'tobacco_misuse_counseling' => $assessment->tobacco_misuse_counseling
                ]);
                $this->careplanRepo->approve($assessment->careplan_id, $assessment->provider_approver_id);
                return $savedAssessments->first();
            }
        }
        else throw new Exception('invalid parameter "assessments"');
    }
}
