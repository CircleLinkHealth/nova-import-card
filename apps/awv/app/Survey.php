<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;
use Illuminate\Support\Collection;

/**
 * Class Survey.
 *
 * @property int id
 * @property string name
 * @property string description
 * @property Collection|SurveyInstance[] instances
 */
class Survey extends BaseModel
{
    const ENROLLEES = 'Enrollees';
    const HRA       = 'HRA';

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

    public function instances()
    {
        return $this->hasMany(SurveyInstance::class, 'survey_id');
    }

    public function scopeHRA($query)
    {
        $query->where('name', self::HRA);
    }

    public function scopeVitals($query)
    {
        $query->where('name', self::VITALS);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_surveys', 'survey_id', 'user_id')
            ->withPivot([
                'survey_instance_id',
                'last_question_answered_id',
                'status',
                'start_date',
                'completed_at',
            ])
            ->withTimestamps();
    }

    public function userSurveyInstances()
    {
        return $this->belongsToMany(SurveyInstance::class, 'users_surveys', 'survey_id', 'survey_instance_id')
            ->withPivot([
                'user_id',
                'status',
                'last_question_answered_id',
                'start_date',
                'completed_at',
            ])
            ->withTimestamps();
    }
}
