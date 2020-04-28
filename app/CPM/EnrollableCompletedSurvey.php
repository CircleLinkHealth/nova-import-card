<?php

namespace App\CPM;

use CircleLinkHealth\Customer\Entities\User;

class EnrollableCompletedSurvey extends AwvToCpmRedisEvent
{
    /**
     * @var User
     */
    private $enrollableId;

    protected $channel = 'enrollable-survey-completed';

    /**
     * EnrollableCompletedSurvey constructor.
     * @param $enrollableId
     */
    public function __construct($enrollableId)
    {
        $this->enrollableId = $enrollableId;
    }

    /**
     * Emits event to CPM through Redis Channel.
     * @param $surveyInstanceId
     */
    public function publishEnrollableCompletedSurvey($surveyInstanceId)
    {
        $this->publish([
            'enrollable_id' => $this->enrollableId,
            'survey_instance_id' => $surveyInstanceId
        ]);
    }
}
