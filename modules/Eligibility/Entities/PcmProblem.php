<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Core\Traits\MySQLSearchable;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Eligibility\Entities\PcmProblem.
 *
 * @property int                                          $id
 * @property int                                          $practice_id
 * @property string                                       $code_type
 * @property string                                       $code
 * @property string                                       $description
 * @property \Illuminate\Support\Carbon|null              $created_at
 * @property \Illuminate\Support\Carbon|null              $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Practice $practice
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem newModelQuery()
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem newQuery()
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem query()
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem whereCode($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem whereCodeType($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem whereCreatedAt($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem whereDescription($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem whereId($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem wherePracticeId($value)
 * @method   static                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\PcmProblem mySQLSearch($columns, $term, $mode = 'BOOLEAN', $shouldRequireAll = true, $shouldRequireIntegers = true)
 */
class PcmProblem extends Model
{
    use MySQLSearchable;

    protected $fillable = [
        'practice_id',
        'code_type',
        'code',
        'description',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }
}
