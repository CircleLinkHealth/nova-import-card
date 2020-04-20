<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ReportSetting.
 *
 * @property int                             $id
 * @property string                          $name
 * @property string|null                     $description
 * @property string                          $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ReportSetting whereValue($value)
 * @mixin \Eloquent
 */
class ReportSetting extends Model
{
    protected $fillable = [
        'name',
        'description',
        'value',
    ];
}
