<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyInstance extends Model
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
        'name',
        'start_date',
        'end_date',
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
                        'status',
                    ])
                    ->withTimestamps();
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'survey_questions', 'survey_instance_id',
            'question_id')->withPivot([
            'order',
        ]);
    }


}
