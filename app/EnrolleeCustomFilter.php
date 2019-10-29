<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Eloquent\Model;

/**
 * App\EnrolleeCustomFilter.
 *
 * @property string                                                                                  $name
 * @property string                                                                                  $type
 * @property int                                                                                     $id
 * @property \Illuminate\Support\Carbon|null                                                         $created_at
 * @property \Illuminate\Support\Carbon|null                                                         $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $practices
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property int|null $practices_count
 */
class EnrolleeCustomFilter extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];

    public function practices()
    {
        return $this->belongsToMany(Practice::class, 'practice_enrollee_filters', 'filter_id', 'practice_id')
            ->withPivot('include');
    }
}
