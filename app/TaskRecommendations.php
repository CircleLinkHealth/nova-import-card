<?php

namespace App;

class TaskRecommendations extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'title',
        'codes',
        'rec_task_titles',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'            => 'array',
        'rec_task_titles' => 'array',
    ];

    protected $table = 'ppp_task_recommendations';
}
