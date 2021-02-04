<?php


namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;


use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

class DuplicatePatientResolver
{
    private const ENROLLED_PATIENT_SCORE = 30;
    private const PATIENT_INFO_SCORE = 2;
    private const CAREPLAN_SCORE = 3;
    private const NOTES_SCORE = 12;
    private const CALLS_SCORE = 5;
    private const FUTURE_CALLS_SCORE = 30;
    
    /**
     * DuplicatePatientResolver constructor.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model $enrollee
     */
    public function __construct($enrollee)
    {}
    
    public function resoveDuplicatePatients(...$userIds)
    {
        $results = collect($users = $this->getUsers($userIds))
                        ->unique()
                        ->filter()
                        ->transform(function (User $user) {
                            return [
                                'user_id' => $user->id,
                                'score' => $this->calculateScore($user),
                            ];
                        })
                        ->sortByDesc('score');
        
        $keep = $results->first();
    }
    
    private function getUsers(array $userIds)
    {
        return User::with([
                              'patientInfo',
                              'carePlan',
                              'inboundCalls',
                              'notes',
                          ])->findMany($userIds);
    }
    
    private function calculateScore(User $user)
    {
        $score = 0;
        
        if (Patient::ENROLLED === $user->patientInfo->ccm_status) {
            $score += self::ENROLLED_PATIENT_SCORE;
        }
        
        if ($user->patientInfo) {
            $score += self::PATIENT_INFO_SCORE;
        }
    
        if ($user->carePlan) {
            $score += self::CAREPLAN_SCORE;
        }
    
        if ($user->notes->isNotEmpty()) {
            $score += self::NOTES_SCORE;
        }
    
        if ($user->calls->isNotEmpty()) {
            $score += self::CALLS_SCORE;
        }
    
        if ($user->calls->where('scheduled_date', '>', now())->isNotEmpty()) {
            $score += self::FUTURE_CALLS_SCORE;
        }
        
        return $score;
    }
}