<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class User.
 *
 * @property Collection|SurveyInstance[] surveyInstances
 * @property Collection|Survey[] surveys
 * @property Answer[]|Collection answers
 * @property Collection|ProviderReport[] providerReports
 * @property InvitationLink url
 * @property PersonalizedPreventionPlan personalizedPreventionPlan
 * @property Patient $patientInfo
 */
class User extends \CircleLinkHealth\Customer\Entities\User
{
    public function addAppointment(Carbon $date, $type = 'awv')
    {
        $this->awvAppointments()->create([
            'type'        => 'awv',
            'appointment' => $date,
        ]);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }

    public function awvAppointments()
    {
        return $this->hasMany(AwvAppointment::class, 'user_id');
    }

    public function getHRAInstances()
    {
        return $this->surveyInstances()->whereHas('survey', function ($survey) {
            $survey->HRA();
        })->get();
    }

    public function getSurveyInstancesBySurveyId($surveyId)
    {
        return $this->surveyInstances()->where('users_surveys.survey_id', $surveyId)->get();
    }

    public function getSurveys()
    {
        return $this->surveys->unique('id');
    }

    public function getVitalsInstances()
    {
        return $this->surveyInstances()->whereHas('survey', function ($survey) {
            $survey->vitals();
        })->get();
    }

    /**
     * @return AwvAppointment|null
     */
    public function latestAwvAppointment()
    {
        return $this->awvAppointments()
            ->orderBy('appointment', 'desc')
            ->first();
    }

    public function personalizedPreventionPlan()
    {
        return $this->hasMany(PersonalizedPreventionPlan::class, 'user_id');
    }

    public function providerReports()
    {
        return $this->hasMany(ProviderReport::class, 'user_id');
    }

    public function surveyInstances()
    {
        return $this->belongsToMany(SurveyInstance::class, 'users_surveys', 'user_id', 'survey_instance_id')
            ->withPivot([
                'survey_id',
                'last_question_answered_id',
                'status',
                'start_date',
                'completed_at',
            ])
            ->withTimestamps();
    }

    public function surveys()
    {
        return $this->belongsToMany(Survey::class, 'users_surveys', 'user_id', 'survey_id')
            ->withPivot([
                'survey_instance_id',
                'status',
                'last_question_answered_id',
                'start_date',
                'completed_at',
            ])
            ->withTimestamps();
    }

    public function url()
    {
        return $this->hasOne(InvitationLink::class);
    }
}
