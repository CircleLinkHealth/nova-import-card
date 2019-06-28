<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

/**
 * CircleLinkHealth\Customer\Entities\CompanyHoliday.
 *
 * @property int                 $id
 * @property string              $holiday_name
 * @property \Carbon\Carbon      $holiday_date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday query()
 */
class CompanyHoliday extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $dates = [
        'holiday_date',
    ];

    protected $fillable = [
        'holiday_date',
        'holiday_name',
    ];
}
