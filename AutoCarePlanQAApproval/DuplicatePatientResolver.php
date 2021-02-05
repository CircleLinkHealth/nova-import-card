<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Rules\PatientIsUnique;
use CircleLinkHealth\SharedModels\Entities\DuplicatePatientResolverLog;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Collection;

class DuplicatePatientResolver
{
    private const CALLS_SCORE            = 5;
    private const CAREPLAN_SCORE         = 3;
    private const ENROLLED_PATIENT_SCORE = 30;
    private const FUTURE_CALLS_SCORE     = 30;
    private const IS_PARTICIPANT         = 10;
    private const IS_SURVEY_ONLY         = 5;
    private const NOTES_SCORE            = 12;
    private const PATIENT_INFO_SCORE     = 2;
    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    protected $enrollee;
    private ?PatientIsUnique $validator = null;

    /**
     * DuplicatePatientResolver constructor.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model $enrollee
     */
    public function __construct(Enrollee &$enrollee)
    {
        $this->enrollee = $enrollee;
    }

    public function duplicateUserIds()
    {
        return $this->validator()->getPatientUserIds()->all();
    }

    public function hasDuplicateUsers()
    {
        return ! $this->validator()->passes(null, null);
    }

    public function resoveDuplicatePatients(...$userIds)
    {
        if (empty($validUserIds = array_filter($userIds))) {
            return;
        }
        
        $results = collect($users = $this->getUsers($userIds))
            ->unique()
            ->filter()
            ->transform(function (User $user) {
                return $this->calculateScore($user);
            })
            ->sortByDesc('score');

        $keep = $results->first()['debug_logs']['patient']['user_id'];
        
        if (count($validUserIds) > 1) {
            $this->storeLogs($results, $keep);
        }
    
        $this->deleteAllExcept($keep, $results->pluck('debug_logs.patient.user_id')->all(), $users);

        $this->enrollee->user_id = $keep;
        $this->enrollee->setRelation('user', $users->where('id', $keep)->first());
    }

    private function calculateScore(User $user)
    {
        $pI = optional($user->patientInfo);
        $score = 0;
        $scoreLogs  = [];

        if ($user->isParticipant()) {
            $score += self::IS_PARTICIPANT;
            $scoreLogs['is_participant'] = self::IS_PARTICIPANT;
        }

        if ($user->isSurveyOnly()) {
            $score += self::IS_SURVEY_ONLY;
            $scoreLogs['is_survey_only'] = self::IS_SURVEY_ONLY;
        }

        if (Patient::ENROLLED === $pI->ccm_status) {
            $score += self::ENROLLED_PATIENT_SCORE;
            $scoreLogs['is_enrolled'] = self::ENROLLED_PATIENT_SCORE;
        }

        if ($pI) {
            $score += self::PATIENT_INFO_SCORE;
            $scoreLogs['has_patient_info'] = self::PATIENT_INFO_SCORE;
        }

        if ($user->carePlan) {
            $score += self::CAREPLAN_SCORE;
            $scoreLogs['has_careplan'] = self::CAREPLAN_SCORE;
        }

        if ($user->notes->isNotEmpty()) {
            $score += self::NOTES_SCORE;
            $scoreLogs['has_notes'] = self::NOTES_SCORE;
        }

        if ($user->calls->isNotEmpty()) {
            $score += self::CALLS_SCORE;
            $scoreLogs['has_calls'] = self::CALLS_SCORE;
        }

        if ($user->calls->where('scheduled_date', '>', now())->isNotEmpty()) {
            $score += self::FUTURE_CALLS_SCORE;
            $scoreLogs['has_future_calls'] = $score;
        }
        
        return [
            'score' => $score,
            'debug_logs'  => [
                'score' => $scoreLogs,
                'patient' => [
                    'user_id' => $user->id,
                    'name' => $user->display_name,
                    'practice_id' => $user->program_id,
                    'patient_info_id' => $pI->id,
                    'dob' => optional($pI->birth_date)->toDateString(),
                    'mrn' => $pI->mrn_number,
                    'careplan_id' => optional($user->carePlan)->id,
                ],
                'enrollee' => [
                    'name' => $this->enrollee->first_name.' '.$this->enrollee->last_name,
                    'practice_id' => $user->practice_id,
                    'dob' => optional($this->enrollee->dob)->toDateString(),
                    'mrn' => $this->enrollee->mrn,
                    'enrollee_id' => $this->enrollee->id,
                ],
                'future_call_ids' => $user->calls->pluck('id')->all(),
                'call_ids' => $user->calls->where('scheduled_date', '>', now())->pluck('id')->all(),
                'note_ids' => $user->notes->pluck('id')
            ],
        ];
    }

    private function deleteAllExcept(int $userIdToKeep, array $allUserIds, \Illuminate\Database\Eloquent\Collection $users)
    {
        foreach ($allUserIds as $uId) {
            if ($uId === $userIdToKeep) {
                continue;
            }

            $deleteUser = $users->where('id', $uId)->first();
            if ($deleteUser) {
                $patientInfo = $deleteUser->patientInfo;
                if ($patientInfo) {
                    $deleted = $patientInfo->delete();
                }
                if ($carePlan = $deleteUser->carePlan) {
                    $deleted = $carePlan->delete();
                }
                $deleted = $deleteUser->delete();
            }
        }
    }

    private function getUsers(array $userIds)
    {
        return User::with([
            'patientInfo',
            'carePlan',
            'inboundCalls',
            'notes',
            'roles',
        ])->findMany($userIds);
    }

    private function validator()
    {
        if (is_null($this->validator)) {
            $enrollee        = $this->enrollee;
            $this->validator = new PatientIsUnique($enrollee->practice_id, $enrollee->first_name, $enrollee->last_name, $enrollee->dob, $enrollee->mrn, $enrollee->user_id);
        }

        return $this->validator;
    }
    
    private function storeLogs(Collection $results, int $userIdToKeep)
    {
        $results->each(function ($debugLog) use ($userIdToKeep) {
            $userId = $debugLog['debug_logs']['patient']['user_id'];
            if ($userIdToKeep === $userId) {
                return;
            }
            
            DuplicatePatientResolverLog::create([
                                                    'user_id_kept' => $userIdToKeep,
                                                    'debug_logs' => $debugLog,
                                                ]);
        });
    }
}
