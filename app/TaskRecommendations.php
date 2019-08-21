<?php

namespace App;

class TaskRecommendations extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'title',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'            => 'array',
    ];

    protected $table = 'ppp_task_recommendations';
}
