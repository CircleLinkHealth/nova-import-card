<?php

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;

class SurveyInstance extends BaseModel
{
    const PENDING = 'pending';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'year',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_surveys', 'survey_instance_id', 'user_id')
                    ->withPivot([
                        'survey_id',
                        'last_question_answered_id',
                        'status',
                        'start_date',
                        'completed_at'
                    ])
                    ->withTimestamps();
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'survey_questions', 'survey_instance_id',
            'question_id')->withPivot([
            'order',
            'sub_order',
        ]);
    }

    public function scopeCurrent($query)
    {
        $query->where('year', Carbon::now()->year);
    }

    public function scopeOfSurvey($query, $surveyName)
    {
        $query->whereHas('survey', function ($survey) use ($surveyName) {
            $survey->where('name', $surveyName);
        });

    }


    public function scopeForYear($query, $year)
    {
        if (is_a($year, Carbon::class)) {
            $year = $year->year;
        }

        $query->where('year', $year);

    }

    public function scopeIsCompletedForPatient($query)
    {
        $query->where('users_surveys.status', SurveyInstance::COMPLETED);
    }


}
