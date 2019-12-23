<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Browser.
 *
 * @property int                        $id
 * @property string                     $name
 * @property string                     $warning_version
 * @property string|null                $required_version
 * @property \Illuminate\Support\Carbon $release_date
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser whereReleaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser whereRequiredVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Browser whereWarningVersion($value)
 * @mixin \Eloquent
 */
class Browser extends Model
{
    protected $dates = [
        'release_date',
    ];
    protected $fillable = [
        'name',
        'warning_version',
        'required_version',
        'release_date',
    ];
}
