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
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday whereHolidayDate($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday whereHolidayName($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CompanyHoliday whereUpdatedAt($value)
 * @property int|null                                                                                    $revision_history_count
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
