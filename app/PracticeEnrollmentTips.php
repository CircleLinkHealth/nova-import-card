<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Enrollment Tips per Practice.
 *
 * @property int practice_id
 * @property string content
 * @property int                                                                            $id
 * @property int                                                                            $practice_id
 * @property string                                                                         $content
 * @property \Illuminate\Support\Carbon|null                                                $created_at
 * @property \Illuminate\Support\Carbon|null                                                $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
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
