<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\EnrollableRequestInfo;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo.
 *
 * @property int                             $id
 * @property int|null                        $enrollable_id
 * @property string|null                     $enrollable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\EnrollableRequestInfo|null $enrollable
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo query()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo whereCreatedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo whereEnrollableId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo whereEnrollableType($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo whereId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrollableRequestInfo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EnrollableRequestInfo extends Model
{
    protected $fillable = [
        'enrollable_id',
        'enrollable_type',
    ];

    protected $table = 'enrollees_request_info';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function enrollable()
    {
        return $this->morphTo();
    }
}
