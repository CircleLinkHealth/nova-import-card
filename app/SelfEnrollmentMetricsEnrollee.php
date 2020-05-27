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
 * @property int|null    $batch_id
 * @property string|null $batch_date
 * @property string|null $batch_time
 * @property string|null $practice_name
 * @property string|null $button_color
 * @property int         $total_invites_sent
 * @property float|null  $total_invites_opened
 * @property float|null  $percentage_invites_opened
 * @property int         $total_saw_letter
 * @property float|null  $percentage_saw_letter
 * @property float|null  $total_saw_form
 * @property float|null  $percentage_saw_form
 * @property float|null  $total_enrolled
 * @property float|null  $percentage_enrolled
 * @property float|null  $total_call_requests
 * @property float|null  $percentage_call_requests
 */
class SelfEnrollmentMetricsEnrollee extends SqlViewModel
{
    protected $primaryKey = 'batch_id';
    protected $table      = 'self_enrollment_metrics_enrollee';
}
