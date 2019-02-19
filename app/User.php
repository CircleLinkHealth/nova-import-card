<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


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
                        'status',
                    ])
                    ->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }


    public function getSurveys()
    {
        //todo:forget pivot?
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
        return $this->surveyInstances()->whereHas('survey', function ($survey) use ($surveyId){
            $survey->where('id', $surveyId);
        })->get();
    }
}
