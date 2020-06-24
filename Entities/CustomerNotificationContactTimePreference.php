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
