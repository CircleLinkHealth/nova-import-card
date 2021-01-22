<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

class TaskRecommendations extends \CircleLinkHealth\Core\Entities\BaseModel
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];
    protected $fillable = [
        'title',
        'data',
    ];

    protected $table = 'ppp_task_recommendations';
}
