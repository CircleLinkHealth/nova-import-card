<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{

    const HRA = 'HRA';

    const VITALS = 'Vitals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'users_surveys', 'survey_id', 'user_id')
                    ->withPivot([
                        'survey_instance_id',
                        'last_question_answered_id',
                        'status',
                    ])
                    ->withTimestamps();
    }

    public function userSurveyInstances()
    {
        return $this->belongsToMany(SurveyInstance::class, 'users_surveys', 'survey_id', 'survey_instance_id')
                    ->withPivot([
                        'user_id',
                        'status',
                    ])
                    ->withTimestamps();
    }

    public function instances()
    {
        return $this->hasMany(SurveyInstance::class, 'survey_id');
    }

    public function scopeHRA($query)
    {
        $query->where('name', Survey::HRA);
    }

    public function scopeVitals($query)
    {
        $query->where('name', Survey::VITALS);
    }

}
