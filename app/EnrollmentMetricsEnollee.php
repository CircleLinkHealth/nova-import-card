<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\EnrollmentMetricsEnollee.
 *
 * @property int|null                                                                                    $batch_id
 * @property string|null                                                                                 $batch_date
 * @property string|null                                                                                 $batch_time
 * @property string|null                                                                                 $practice_name
 * @property string|null                                                                                 $button_color
 * @property int                                                                                         $total_invites_sent
 * @property float|null                                                                                  $total_invites_opened
 * @property float|null                                                                                  $percentage_invites_opened
 * @property int                                                                                         $total_saw_letter
 * @property float|null                                                                                  $percentage_saw_letter
 * @property float|null                                                                                  $total_saw_form
 * @property float|null                                                                                  $percentage_saw_form
 * @property float|null                                                                                  $total_enrolled
 * @property float|null                                                                                  $percentage_enrolled
 * @property float|null                                                                                  $total_call_requests
 * @property float|null                                                                                  $percentage_call_requests
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrollmentMetricsEnollee newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrollmentMetricsEnollee newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrollmentMetricsEnollee query()
 * @mixin \Eloquent
 */
class EnrollmentMetricsEnollee extends BaseModel
{
    protected $table = 'self_enrollment_metrics_enrollee';
}
