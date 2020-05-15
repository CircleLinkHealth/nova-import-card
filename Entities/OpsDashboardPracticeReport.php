<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

class OpsDashboardPracticeReport extends Model
{
    protected $casts = [
        'data' => 'array',
    ];
    protected $dates = [
        'date',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'practice_id',
        'date',
        'data',
        'is_processed',
    ];

    protected $table = 'ops_dashboard_practice_reports';

    public function practice()
    {
        return $this->belongsTo(Practice::class, 'practice_id');
    }
}
