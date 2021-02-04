<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

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
     * DuplicatePatientResolver constructor.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model $enrollee
     */
    public function __construct($enrollee)
    {
    }

    public function resoveDuplicatePatients(...$userIds)
    {
        $results = collect($users = $this->getUsers($userIds))
            ->unique()
            ->filter()
            ->transform(function (User $user) {
                            return [
                                'user_id' => $user->id,
                                'score'   => $this->calculateScore($user)['score'],
                                'logs'   => $this->calculateScore($user)['logs'],
                            ];
                        })
            ->sortByDesc('score');

        $keep = $results->first();
    }

    private function calculateScore(User $user)
    {
        $score = 0;
        $logs  = [];

        if ($user->isParticipant()) {
            $score += self::IS_PARTICIPANT;
            $logs['is_participant'] = $score;
        }

        if ($user->isSurveyOnly()) {
            $score += self::IS_SURVEY_ONLY;
            $logs['is_survey_only'] = $score;
        }

        if (Patient::ENROLLED === $user->patientInfo->ccm_status) {
            $score += self::ENROLLED_PATIENT_SCORE;
            $logs['is_enrolled'] = $score;
        }

        if ($user->patientInfo) {
            $score += self::PATIENT_INFO_SCORE;
            $logs['has_patient_info'] = $score;
        }

        if ($user->carePlan) {
            $score += self::CAREPLAN_SCORE;
            $logs['has_careplan'] = $score;
        }

        if ($user->notes->isNotEmpty()) {
            $score += self::NOTES_SCORE;
            $logs['has_notes'] = $score;
        }

        if ($user->calls->isNotEmpty()) {
            $score += self::CALLS_SCORE;
            $logs['has_calls'] = $score;
        }

        if ($user->calls->where('scheduled_date', '>', now())->isNotEmpty()) {
            $score += self::FUTURE_CALLS_SCORE;
            $logs['has_future_calls'] = $score;
        }

        return [
            'score' => $score,
            'logs'  => $logs,
        ];
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
}
