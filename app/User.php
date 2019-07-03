<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class User
 * @package App
 *
 * @property-read SurveyInstance[]|Collection surveyInstances
 * @property-read Survey[]|Collection surveys
 * @property-read Answer[]|Collection answers
 * @property-read ProviderReport[]|Collection providerReports
 * @property-read InvitationLink url
 * @property-read PersonalizedPreventionPlan personalizedPreventionPlan
 *
 */
class User extends \CircleLinkHealth\Customer\Entities\User
{
    public function url()
    {
        return $this->hasOne(InvitationLink::class);
    }

    public function surveys()
    {
        return $this->belongsToMany(Survey::class, 'users_surveys', 'user_id', 'survey_id')
                    ->withPivot([
                        'survey_instance_id',
                        'status',
                        'last_question_answered_id',
                        'start_date',
                        'completed_at'
                    ])
                    ->withTimestamps();
    }

    public function surveyInstances()
    {
        return $this->belongsToMany(SurveyInstance::class, 'users_surveys', 'user_id', 'survey_instance_id')
                    ->withPivot([
                        'survey_id',
                        'last_question_answered_id',
                        'status',
                        'start_date',
                        'completed_at'
                    ])
                    ->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }

    public function providerReports()
    {
        return $this->hasMany(ProviderReport::class, 'user_id');
    }

    public function getSurveys()
    {
        return $this->surveys->unique('id');
    }

    public function getHRAInstances()
    {
        return $this->surveyInstances()->whereHas('survey', function ($survey) {
            $survey->HRA();
        })->get();
    }

    public function getVitalsInstances()
    {
        return $this->surveyInstances()->whereHas('survey', function ($survey) {
            $survey->vitals();
        })->get();
    }

    public function getSurveyInstancesBySurveyId($surveyId)
    {
        return $this->surveyInstances()->where('users_surveys.survey_id', $surveyId)->get();
    }

    public function personalizedPreventionPlan()
    {
        return $this->hasOne(PersonalizedPreventionPlan::class, 'user_id');
    }
}
