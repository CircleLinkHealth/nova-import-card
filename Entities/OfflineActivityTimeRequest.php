<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Entities;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Services\ActivityService;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

/**
 * CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest.
 *
 * @property int                                                   $id
 * @property int|null                                              $is_approved
 * @property int                                                   $is_behavioral
 * @property string|null                                           $type
 * @property int                                                   $duration_seconds
 * @property int                                                   $patient_id
 * @property int                                                   $requester_id
 * @property int|null                                              $activity_id
 * @property \Illuminate\Support\Carbon|null                       $performed_at
 * @property string|null                                           $comment
 * @property \Illuminate\Support\Carbon|null                       $created_at
 * @property \Illuminate\Support\Carbon|null                       $updated_at
 * @property \Illuminate\Support\Carbon|null                       $deleted_at
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity|null $activity
 * @property \CircleLinkHealth\Customer\Entities\User              $patient
 * @property \CircleLinkHealth\Customer\Entities\User              $requester
 * @method   static                                                bool|null forceDelete()
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     newModelQuery()
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     newQuery()
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     onlyTrashed()
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest query()
 * @method static bool|null restore()
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereActivityId($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereComment($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereCreatedAt($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereDeletedAt($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereDurationSeconds($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereId($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereIsApproved($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereIsBehavioral($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     wherePatientId($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     wherePerformedAt($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereRequesterId($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereType($value)
 * @method static
 *     \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest
 *     withoutTrashed()
 * @mixin \Eloquent
 */
class OfflineActivityTimeRequest extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at', 'performed_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'comment',
        'duration_seconds',
        'patient_id',
        'requester_id',
        'is_approved',
        'is_behavioral',
        'performed_at',
        'activity_id',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function approve()
    {
        // Send a request to the time-tracking server to increment the start-time by the duration of the offline-time activity (in seconds)
        $client = new Client();

        $url = config('services.ws.server-url').'/'.$this->requester_id.'/'.$this->patient_id;

        try {
            $timeParam = $this->is_behavioral
                ? 'bhiTime'
                : 'ccmTime';
            $res = $client->put(
                $url,
                [
                    'form_params' => [
                        'startTime' => $this->duration_seconds,
                        $timeParam  => $this->duration_seconds,
                    ],
                ]
            );
            $status = $res->getStatusCode();
            $body   = $res->getBody();
            if (200 == $status) {
                Log::info($body);
            } else {
                Log::critical($body);
            }
        } catch (\Exception $ex) {
            Log::critical($ex);
        }

        $cs = ChargeableService::firstWhere('code', '=', $this->is_behavioral ? ChargeableService::BHI : ChargeableService::CCM);

        $activityService            = app(ActivityService::class);
        $chargeableServicesDuration = $activityService->separateDurationForEachChargeableServiceId($this->patient, $this->duration_seconds, $cs->id);
        foreach ($chargeableServicesDuration as $chargeableServiceDuration) {
            $pageTimer                = new PageTimer();
            $pageTimer->activity_type = $this->type;
            $pageTimer->patient_id    = $this->patient_id;
            $pageTimer->provider_id   = $this->requester_id;
            $pageTimer->start_time    = $this->performed_at->toDateTimeString();
            $activity                 = app(PatientServiceProcessorRepository::class)->createActivityForChargeableService('manual_input', $pageTimer, $chargeableServiceDuration);
        }

        $nurse = optional($this->requester)->nurseInfo;

        event(new PatientActivityCreated($this->patient_id));
        $activityService->processMonthlyActivityTime($this->patient_id, $this->performed_at);

        if ($nurse) {
            (new AlternativeCareTimePayableCalculator())->adjustNursePayForActivity($nurse->id, $activity);
        }

        $this->is_approved = true;
        $this->activity_id = $activity->id;
        $this->save();
    }

    public function durationInMinutes()
    {
        return $this->duration_seconds / 60;
    }

    public function getStatusCssClass()
    {
        if (is_null($this->is_approved)) {
            return 'warning';
        }

        if (true === (bool) $this->is_approved) {
            return 'success';
        }

        if (false === (bool) $this->is_approved) {
            return 'danger';
        }
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function reject()
    {
        $this->is_approved = false;
        $this->save();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function status()
    {
        if (is_null($this->is_approved)) {
            return 'PENDING';
        }

        if (true === (bool) $this->is_approved) {
            return 'APPROVED';
        }

        if (false === (bool) $this->is_approved) {
            return 'REJECTED';
        }
    }
}
