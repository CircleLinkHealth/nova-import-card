<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Eligibility\Entities\RpmProblem.
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
 */
class RpmProblem extends Model
{
    protected $fillable = [
        'practice_id',
        'code_type',
        'code',
        'description',
    ];
}
