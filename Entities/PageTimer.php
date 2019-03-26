<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Entities;

use App\Scopes\Universal\DateScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\TimeTracking\Entities\PageTimer.
 *
 * @property int                                                      $id
 * @property int                                                      $billable_duration
 * @property int                                                      $duration
 * @property string|null                                              $duration_unit
 * @property int                                                      $patient_id
 * @property int                                                      $provider_id
 * @property string                                                   $start_time
 * @property string                                                   $end_time
 * @property string|null                                              $redirect_to
 * @property string|null                                              $url_full
 * @property string|null                                              $url_short
 * @property string                                                   $activity_type
 * @property string                                                   $title
 * @property string                                                   $query_string
 * @property int                                                      $program_id
 * @property string|null                                              $ip_addr
 * @property \Carbon\Carbon                                           $created_at
 * @property \Carbon\Carbon                                           $updated_at
 * @property string|null                                              $processed
 * @property string|null                                              $rule_params
 * @property int|null                                                 $rule_id
 * @property \Carbon\Carbon|null                                      $deleted_at
 * @property string|null                                              $user_agent
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection $activities
 * @property \CircleLinkHealth\Customer\Entities\User                                                $logger
 * @property \CircleLinkHealth\Customer\Entities\User                                                $patient
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdThisMonth($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdOn(Carbon $date, $field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdToday($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdYesterday($field = 'created_at')
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PageTimer onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereActivityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereActualEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereActualStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereBillableDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereDurationUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereIpAddr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereQueryString($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereRedirectTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereRuleParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUrlFull($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUrlShort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUserAgent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PageTimer withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PageTimer withoutTrashed()
 * @mixin \Eloquent
 */
class PageTimer extends \App\BaseModel
{
    use DateScopesTrait, SoftDeletes;

    protected $dates = ['deleted_at', 'start_time', 'end_time'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'billable_duration',
        'duration',
        'duration_unit',
        'patient_id',
        'provider_id',
        'start_time',
        'end_time',
        'redirect_to',
        'url_full',
        'url_short',
        'program_id',
        'ip_addr',
        'user_agent',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_page_timer';

    public function activities()
    {
        return $this->hasMany('CircleLinkHealth\TimeTracking\Entities\Activity', 'page_timer_id');
    }

    public function activity()
    {
        return $this->belongsTo('CircleLinkHealth\TimeTracking\Entities\Activity', 'id', 'page_timer_id');
    }

    public function logger()
    {
        return $this->belongsTo('CircleLinkHealth\Customer\Entities\User', 'provider_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo('CircleLinkHealth\Customer\Entities\User', 'patient_id', 'id');
    }
}
