<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;

/**
 * App\SelfEnrollmentMetricsEnrollee.
 *
 * @property string|null $color
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\SelfEnrollmentMetricsEnrollee newModelQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\SelfEnrollmentMetricsEnrollee newQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\SelfEnrollmentMetricsEnrollee query()
 * @mixin \Eloquent
 */
class SelfEnrollmentMetricsEnrollee extends SqlViewModel
{
    protected $primaryKey = 'batch_id';
    protected $table      = 'self_enrollment_metrics_enrollee';
}
