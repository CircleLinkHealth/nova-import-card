<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\CustomerNotificationContactTimePreference.
 *
 * @property int                             $id
 * @property string                          $contactable_type
 * @property int                             $contactable_id
 * @property string                          $notification
 * @property string                          $day
 * @property string                          $from
 * @property string                          $to
 * @property int|null                        $max_per_hour
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomerNotificationContactTimePreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomerNotificationContactTimePreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CustomerNotificationContactTimePreference query()
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @property int                                                                                         $is_enabled
 */
class CustomerNotificationContactTimePreference extends BaseModel
{
    const AUDIT_REPORTS_FAXES_PER_HOUR = 'audit_report_faxes_per_hour';

    const VALID_CACHE_KEY_OPTIONS = [
        self::AUDIT_REPORTS_FAXES_PER_HOUR,
    ];

    protected $fillable = [
        'contactable_type',
        'contactable_id',
        'notification',
        //Monday,Tuesday,etc
        'day',
        //in 'America/New_York'
        'from',
        //in 'America/New_York'
        'to',
        'is_enabled',
        'max_per_hour',
    ];

    public function cacheKey(string $cacheKeyOption)
    {
        if ( ! in_array($cacheKeyOption, self::VALID_CACHE_KEY_OPTIONS)) {
            throw new \InvalidArgumentException("`$cacheKeyOption` is not a valid option. Valid options are ".json_encode(self::VALID_CACHE_KEY_OPTIONS));
        }

        return $cacheKeyOption.$this->contactable_type.$this->contactable_id.now()->startOfHour()->format('Y-m-d H:i');
    }

    /**
     * Always defaults to the app timezone, ie 'America/New_York'.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function timezone()
    {
        return config('app.timezone');
    }
}
