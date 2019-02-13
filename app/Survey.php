<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{

    const HRA = 'hra';

    const VITALS = 'vitals';

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
        return $this->belongsToMany(User::class, 'users_surveys', 'survey_id', 'user_id')->withPivot([
            'survey_instance_id',
            'status',
        ]);
    }

    public function instances()
    {
        return $this->hasMany(SurveyInstance::class, 'survey_id');
    }

}
