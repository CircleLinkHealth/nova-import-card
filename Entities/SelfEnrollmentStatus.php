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
 * @property int|null                                                                                    $enrollee_patient_info
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
//    We could save all needed values for the enrolee invitation dashboard on this model.
//    But i think is better if they re in sync with the relationships that are used
//    in a lot of places in the app.
    protected $fillable = [
        'enrollee_id',
        'enrollee_user_id',
        'enrollee_patient_info',
        'awv_survey_status',
        'logged_in',
    ];
    protected $table = 'self_enrollment_statuses';
}
