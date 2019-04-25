<?php

namespace App;

/**
 * @property mixed surveyInstances
 * @property mixed answers
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
                    ])
                    ->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }

    public function providerReports(){
        return $this->hasMany(ProviderReport::class, 'patient_id');
    }

    public function getSurveys()
    {
        return $this->surveys->unique('id');
    }

    public function getHRAInstances(){
        return $this->surveyInstances()->whereHas('survey', function ($survey){
            $survey->HRA();
        })->get();
    }

    public function getVitalsInstances(){
        return $this->surveyInstances()->whereHas('survey', function ($survey){
            $survey->vitals();
        })->get();
    }

    public function getSurveyInstancesBySurveyId($surveyId){
        return $this->surveyInstances()->where('users_surveys.survey_id', $surveyId)->get();
    }

    public function personalizedPreventionPlan() {
        return $this->hasOne(PersonalizedPreventionPlan::class, 'patient_id');
    }
}
