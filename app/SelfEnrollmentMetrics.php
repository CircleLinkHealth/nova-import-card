<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;

/**
 * App\SelfEnrollmentMetrics.
 *
 * @property string|null $color
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\SelfEnrollmentMetrics newModelQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\SelfEnrollmentMetrics newQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\SelfEnrollmentMetrics query()
 * @mixin \Eloquent
 */
class SelfEnrollmentMetrics extends SqlViewModel
{
    protected $table = 'self_enrollment_metrics_view';
}
