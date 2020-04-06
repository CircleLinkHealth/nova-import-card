<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;
use Illuminate\Support\Collection;

/**
 * Class Survey
 *
 * @property int id
 * @property string name
 * @property string description
 * @property-read SurveyInstance[]|Collection instances
 *
 * @package App
 */
class Survey extends BaseModel
{

    const HRA = 'HRA';

    const VITALS = 'Vitals';

    const ENROLLEES = 'Enrollees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_surveys', 'survey_id', 'user_id')
                    ->withPivot([
                        'survey_instance_id',
                        'last_question_answered_id',
                        'status',
                        'start_date',
                        'completed_at'
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
                        'completed_at'
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
