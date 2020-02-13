<?php


namespace App\CPM;


use CircleLinkHealth\Customer\Entities\User;

class EnrollableCompletedSurvey extends AwvToCpmRedisEvent
{
    /**
     * @var User
     */
    private $enrollable;

    protected $channel = 'enrollable-survey-completed';

    /**
     * EnrollableCompletedSurvey constructor.
     * @param User $enrollable
     */
    public function __construct(User $enrollable)
    {
        $this->enrollable = $enrollable;
    }

    /**
     * Emits event to CPM through Redis Channel
     * @param $surveyInstanceId
     */
    public function publishEnrollableCompletedSurvey($surveyInstanceId){

        $this->publish([
            'enrollable_id' => $this->enrollable->id,
            'survey_instance_id' => $surveyInstanceId
        ]);
    }
}
