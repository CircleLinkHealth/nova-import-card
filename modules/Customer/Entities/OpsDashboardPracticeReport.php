<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport.
 *
 * @property int                                          $id
 * @property int                                          $practice_id
 * @property \Illuminate\Support\Carbon                   $date
 * @property array|null                                   $data
 * @property int                                          $is_processed
 * @property \Illuminate\Support\Carbon|null              $created_at
 * @property \Illuminate\Support\Carbon|null              $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Practice $practice
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport newModelQuery()
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport newQuery()
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport query()
 * @mixin \Eloquent
 */
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
