<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\Eligibility\Entities\SelfEnrollmentStatus.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $enrollee_id
 * @property int|null                                                                                    $enrollee_user_id
 * @property string|null                                                                                 $awv_survey_status
 * @property int                                                                                         $logged_in
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\SelfEnrollmentStatus newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\SelfEnrollmentStatus newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\SelfEnrollmentStatus query()
 * @mixin \Eloquent
 */
class SelfEnrollmentStatus extends BaseModel
{
    protected $fillable = [
        'enrollee_id',
        'user_id_from_enrollee',
        'awv_survey_status',
        'logged_in',
    ];
    protected $table = 'self_enrollment_statuses';
}
