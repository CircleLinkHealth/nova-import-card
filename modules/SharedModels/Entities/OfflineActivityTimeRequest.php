<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Jobs\ProcessMonthltyPatientTime;
use CircleLinkHealth\Customer\Jobs\ProcessNurseMonthlyLogs;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use CircleLinkHealth\Timetracking\Services\TimeTrackerServerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest.
 *
 * @property int                                                   $id
 * @property int|null                                              $is_approved
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
 * @property \CircleLinkHealth\SharedModels\Entities\Activity|null $activity
 * @property \CircleLinkHealth\Customer\Entities\User              $patient
 * @property \CircleLinkHealth\Customer\Entities\User              $requester
 * @method static                                                bool|null forceDelete()
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
 * @property int|null               $chargeable_service_id
 * @property ChargeableService|null $chargeableService
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
        'chargeable_service_id',
        'performed_at',
        'activity_id',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * @throws \Exception
     */
    public function approve()
    {
        if ( ! $this->chargeable_service_id) {
            $this->setChargeableServiceIdBasedOnPatientConditions();
        }

        app(TimeTrackerServerService::class)->syncOfflineTime($this);

        $nurse                     = optional($this->requester)->nurseInfo;
        $activityService           = app(ActivityService::class);
        $chargeableServiceDuration = $activityService->getChargeableServiceIdDuration($this->patient, $this->duration_seconds, $this->chargeable_service_id);
        $pageTimer                 = new PageTimer();
        $pageTimer->activity_type  = $this->type;
        $pageTimer->patient_id     = $this->patient_id;
        $pageTimer->provider_id    = $this->requester_id;
        $pageTimer->start_time     = $this->performed_at->toDateTimeString();
        $activity                  = app(PatientServiceProcessorRepository::class)->createActivityForChargeableService('manual_input', $pageTimer, $chargeableServiceDuration);

        if ( ! empty($this->comment)) {
            $meta = new ActivityMeta(['meta_key' => 'comment', 'meta_value' => $this->comment]);
            $activity->meta()->save($meta);
        }

        ProcessMonthltyPatientTime::dispatchNow($this->patient_id);
        if ($nurse) {
            ProcessNurseMonthlyLogs::dispatchNow($activity);
        }

        $this->is_approved = true;
        $this->activity_id = $activity->id;
        $this->save();

        event(new PatientActivityCreated($this->patient_id, false));
    }

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id');
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

    /**
     * For backwards compatibility, in case we have requests without a chargeable service id.
     */
    private function setChargeableServiceIdBasedOnPatientConditions()
    {
        // for backwards compatibility
        $patient = User::withTrashed()->find($this->patient_id);
        /** @var string $code */
        $code = null;
        if ($patient->isCcm()) {
            $code = ChargeableService::CCM;
        } elseif ($patient->isBhi()) {
            $code = ChargeableService::BHI;
        } elseif ($patient->isPcm()) {
            $code = ChargeableService::PCM;
        } elseif ($patient->isRpm()) {
            $code = ChargeableService::RPM;
        } elseif ($patient->isRhc()) {
            $code = ChargeableService::GENERAL_CARE_MANAGEMENT;
        }

        if ( ! $code) {
            throw new \Exception("could not assign chargeable service to offline activity time request[$this->id]");
        }

        $this->chargeable_service_id = ChargeableService::cached()->firstWhere('code', $code)->id;
    }
}
