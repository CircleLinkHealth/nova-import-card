<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ConditionCodeLookup\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ConditionCodeLookupCache.
 *
 * @property int            $id
 * @property string         $type
 * @property string         $code
 * @property string         $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string         $request_url
 * @property string         $response_raw
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereRequestUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereResponseRaw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\ConditionCodeLookup\Entities\ConditionCodeLookupCache whereUpdatedAt($value)
 */
class ConditionCodeLookupCache extends Model
{
    protected $fillable = [
        'type',
        'code',
        'name',
        'request_url',
        'response_raw',
    ];
    protected $table = 'condition_code_lookup_cache';
}
