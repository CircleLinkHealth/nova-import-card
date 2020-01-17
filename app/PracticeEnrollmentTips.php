<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Enrollment Tips per Practice.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $practice_id
 * @property string                                                                                      $content
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property int|null $revision_history_count
 */
class PracticeEnrollmentTips extends BaseModel
{
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'practice_id',
        'content',
    ];
}
