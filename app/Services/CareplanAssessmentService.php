<?php namespace App\Services;

use App\Note;
use App\User;
use App\Patient;
use App\CareplanAssessment;
use App\Repositories\CareplanRepository;
use App\Repositories\CareplanAssessmentRepository;
use App\Repositories\NoteRepository;
use App\Repositories\PatientWriteRepository;
use App\Notifications\SendAssessmentNotification;

class CareplanAssessmentService
{
    private $assessmentRepo;
    private $careplanRepo;
    private $noteRepo;
    private $patientRepo;

    public function __construct(
        CareplanAssessmentRepository $assessmentRepo,
                                    CareplanRepository $careplanRepo,
                                        NoteRepository $noteRepo,
                                            PatientWriteRepository $patientRepo
    ) {
        $this->assessmentRepo = $assessmentRepo;
        $this->careplanRepo = $careplanRepo;
        $this->noteRepo = $noteRepo;
        $this->patientRepo = $patientRepo;
    }

    public function repo()
    {
        return $this->assessmentRepo;
    }

    public function exists($careplanId, $approverId)
    {
        return !!$this->repo()->model()->where([ 'careplan_id' => $careplanId, 'provider_approver_id' => $approverId ])->first();
    }

    public function createAssessmentNote(CareplanAssessment $assessment, $body, $type)
    {
        $note = new Note();
        $note->patient_id = $assessment->careplan_id;
        $note->author_id = $assessment->provider_approver_id;
        $note->body = $body;
        $note->type = $type;
        return $this->noteRepo->add($note);
    }

    public function after(CareplanAssessment $assessment)
    {
        $this->createAssessmentNote($assessment, $assessment->note(), 'Enrollment');

        $practice = $assessment->approver()->first()->practices()->first();
        if ($practice) {
            $location = $practice->primaryLocation()->first();
            if ($location) {
                $location->notify(new SendAssessmentNotification($assessment));
            }
        }
    }

    public function save(CareplanAssessment $assessment)
    {
        if ($assessment) {
            if (!$this->exists($assessment->careplan_id, $assessment->provider_approver_id)) {
                $assessment->save();
                $this->after($assessment);
                return $assessment;
            } else {
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

                $this->after($assessment);

                return $savedAssessments->first();
            }
        } else {
            throw new Exception('invalid parameter "assessments"');
        }
    }
}
