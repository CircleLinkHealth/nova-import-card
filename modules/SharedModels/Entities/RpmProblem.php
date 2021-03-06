<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\SharedModels\Entities\RpmProblem.
 *
 * @property int                             $id
 * @property int                             $practice_id
 * @property string                          $code_type
 * @property string                          $code
 * @property string                          $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|RpmProblem newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|RpmProblem newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|RpmProblem query()
 * @mixin \Eloquent
 * @property Practice $practice
 */
class RpmProblem extends Model
{
    protected $fillable = [
        'practice_id',
        'code_type',
        'code',
        'description',
    ];

    public function practice()
    {
        return $this->belongsTo(Practice::class, 'practice_id');
    }
}
