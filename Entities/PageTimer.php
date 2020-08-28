<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\TimeTracking\Entities\PageTimer.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $billable_duration
 * @property int                                                                                         $duration
 * @property string|null                                                                                 $duration_unit
 * @property int                                                                                         $patient_id
 * @property int                                                                                         $enrollee_id
 * @property int                                                                                         $provider_id
 * @property string                                                                                      $start_time
 * @property string                                                                                      $end_time
 * @property string|null                                                                                 $redirect_to
 * @property string|null                                                                                 $url_full
 * @property string|null                                                                                 $url_short
 * @property string                                                                                      $activity_type
 * @property string                                                                                      $title                                                                                         Title is actually route name or uri
 * @property string                                                                                      $query_string
 * @property int                                                                                         $program_id
 * @property string|null                                                                                 $ip_addr
 * @property \Carbon\Carbon                                                                              $created_at
 * @property \Carbon\Carbon                                                                              $updated_at
 * @property string|null                                                                                 $processed
 * @property string|null                                                                                 $rule_params
 * @property int|null                                                                                    $rule_id
 * @property \Carbon\Carbon|null                                                                         $deleted_at
 * @property string|null                                                                                 $user_agent
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection $activities
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $logger
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $patient
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdThisMonth($field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdOn(Carbon $date, $field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdToday($field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdYesterday($field = 'created_at')
 * @method   static                                                                                      bool|null forceDelete()
 * @method   static                                                                                      \Illuminate\Database\Query\Builder|PageTimer onlyTrashed()
 * @method   static                                                                                      bool|null restore()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereActivityType($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereActualEndTime($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereActualStartTime($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereBillableDuration($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereDeletedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereDuration($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereDurationUnit($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereEndTime($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereIpAddr($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer wherePatientId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereProcessed($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereProgramId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereProviderId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereQueryString($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereRedirectTo($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereRuleId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereRuleParams($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereStartTime($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereTitle($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUpdatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUrlFull($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUrlShort($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUserAgent($value)
 * @method   static                                                                                      \Illuminate\Database\Query\Builder|PageTimer withTrashed()
 * @method   static                                                                                      \Illuminate\Database\Query\Builder|PageTimer withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity                                            $activity
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer
 *     createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer
 *     newModelQuery()
 * @method   static                                                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer newQuery()
 * @method   static                                                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer query()
 * @property int|null                                                                     $activities_count
 * @property int|null                                                                     $revision_history_count
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Nova\Actions\ActionEvent[] $actions
 * @property int|null                                                                     $actions_count
 * @method   static                                                                       \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer whereEnrolleeId($value)
 * @property int|null                                                                     $chargeable_service_id
 * @property ChargeableService|null                                                       $chargeableService
 */
class PageTimer extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use \Laravel\Nova\Actions\Actionable;
    use DateScopesTrait;
    use SoftDeletes;

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
        'chargeable_service_id',
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

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id');
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
