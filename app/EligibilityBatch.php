<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EligibilityBatch extends Model
{
    const TYPE_GOOGLE_DRIVE = 'google_drive';

    const STATUSES = [
        'not_started' => 0,
        'processing'  => 1,
        'error'       => 2,
        'complete'    => 3,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];

    protected $fillable = [
        'type',
        'options',
        'status',
    ];

    protected $attributes = [
        'status' => 0
    ];
}
