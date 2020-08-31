<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Nurseinvoices\TimeSplitter;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\ParameterBag;

class StoreTimeTracking implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Do not count time for these routes
     * force_skip is set in {@link ProviderUITimerComposer}.
     */
    const UNTRACKED_ROUTES = [
        'patient.activity.create',
        'patient.activity.providerUIIndex',
        'patient.reports.progress',
    ];

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * @var ParameterBag
     */
    protected $params;

    private ?bool $isPatientBhi = null;

    /**
     * Create a new job instance.
     */
    public function __construct(ParameterBag $params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        /** @var User $provider */
        $provider = User::findOrFail($this->params->get('providerId', null));

        $patientId = $this->params->get('patientId', 0);
        $patient   = $this->getPatient($patientId);

        foreach ($this->params->get('activities', []) as $activity) {
            $isBehavioral = isset($activity['is_behavioral'])
                ? (bool) $activity['is_behavioral'] && $this->isPatientBhi($patient)
                : false;

            $pageTimer = $this->createPageTimer($activity);

            if ($this->isBillableActivity($pageTimer, $activity, $provider)) {
                $this->processBillableActivity($patient, $pageTimer, $isBehavioral);
            }

            if ($this->isProcessableCareAmbassadorActivity($activity, $provider)) {
                ProcessCareAmbassadorTime::dispatchNow($provider->id, $activity);
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'storetime',
            'patient:'.$this->params->get('patientId'),
            'provider:'.$this->params->get('providerId', null),
        ];
    }

    /**
     * Create a PageTimer.
     *
     * @param $activity
     *
     * @return PageTimer
     */
    private function createPageTimer(array $activity)
    {
        $duration = $activity['duration'];

        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $activity['start_time']);
        $endTime   = $startTime->copy()->addSeconds($duration);
        if (isset($activity['end_time'])) {
            try {
                $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $activity['end_time']);
            } catch (\Throwable $e) {
                Log::warning('Could not read activity[end_time]: '.$e->getMessage());
            }
        }

        $pageTimer                    = new PageTimer();
        $pageTimer->redirect_to       = $this->params->get('redirectLocation', null);
        $pageTimer->billable_duration = $duration;
        $pageTimer->duration          = $duration;
        $pageTimer->duration_unit     = 'seconds';
        $pageTimer->patient_id        = $this->params->get('patientId');
        $pageTimer->enrollee_id       = empty($activity['enrolleeId']) ? null : $activity['enrolleeId']; //0 is null
        $pageTimer->provider_id       = $this->params->get('providerId', null);
        $pageTimer->start_time        = $startTime->toDateTimeString();
        $pageTimer->end_time          = $endTime->toDateTimeString();
        $pageTimer->url_full          = $activity['url'];
        $pageTimer->url_short         = $activity['url_short'];
        $pageTimer->program_id        = empty($this->params->get('programId', null)) ? null : $this->params->get('programId', null);
        $pageTimer->ip_addr           = $this->params->get('ipAddr');
        $pageTimer->activity_type     = $activity['name'];
        $pageTimer->title             = $activity['title'];
        $pageTimer->user_agent        = $this->params->get('userAgent', null);
        $pageTimer->save();

        return $pageTimer;
    }

    private function getChargeServiceIdByCode(User $patient, string $code): ?int
    {
        return optional($patient
            ->primaryPractice
            ->chargeableServices
            ->where('code', '=', $code)
            ->first())
            ->id;
    }

    private function getPatient($patientUserId): ?User
    {
        if (empty($patientUserId)) {
            return null;
        }

        return User::without(['roles', 'perms'])
            ->with([
                'ccdProblems.cpmProblem',
                'primaryPractice.chargeableServices',
                'patientSummaries' => function ($q) {
                    $q->where('month_year', '=', now()->startOfMonth());
                },
            ])
            ->find($patientUserId);
    }

    /**
     * Returns true if an activity should be created, and false if it should not.
     *
     * @return bool
     */
    private function isBillableActivity(PageTimer $pageTimer, array $activity, User $provider)
    {
        $forceSkip = $activity['force_skip'] ?? false;

        return ! ( ! (bool) $provider->isCCMCountable()
                   || 0 == $pageTimer->patient_id
                   || in_array($pageTimer->title, self::UNTRACKED_ROUTES)
                   || $forceSkip);
    }

    private function isPatientBhi(User $patient = null): bool
    {
        if ( ! is_null($this->isPatientBhi)) {
            return $this->isPatientBhi;
        }

        $this->isPatientBhi = ! empty($patient) && $patient->isBhi();

        return $this->isPatientBhi;
    }

    /**
     * If user is a care ambassador, then we should process their time in CA logs.
     * Unless activity is marked in {@link UNTRACKED_CA_ACTIVITIES}.
     *
     * @return bool
     */
    private function isProcessableCareAmbassadorActivity(array $activity, User $provider)
    {
        $forceSkip = $activity['force_skip'] ?? false;

        return ! $forceSkip && $provider->isCareAmbassador();
    }

    /**
     * Create an Activity.
     *
     * @param bool $isBehavioral
     *
     * @return Activity|\Illuminate\Database\Eloquent\Model
     */
    private function processBillableActivity(User $patient, PageTimer $pageTimer, $isBehavioral = false): void
    {
        $chargeableServicesDuration = $this->separateDurationForEachChargeableServiceId($patient, $pageTimer->duration, $isBehavioral);
        foreach ($chargeableServicesDuration as $chargeableServiceDuration) {
            $activity = Activity::create(
                [
                    'type'                  => $pageTimer->activity_type,
                    'provider_id'           => $pageTimer->provider_id,
                    'is_behavioral'         => $isBehavioral,
                    'performed_at'          => $pageTimer->start_time,
                    'duration'              => $chargeableServiceDuration->duration,
                    'duration_unit'         => 'seconds',
                    'patient_id'            => $pageTimer->patient_id,
                    'logged_from'           => 'pagetimer',
                    'logger_id'             => $pageTimer->provider_id,
                    'page_timer_id'         => $pageTimer->id,
                    'chargeable_service_id' => $chargeableServiceDuration->id,
                ]
            );
            ProcessMonthltyPatientTime::dispatchNow($patient->id);
            ProcessNurseMonthlyLogs::dispatchNow($activity);
        }
    }

    private function separateDurationForEachChargeableServiceId(User $patient, $duration, $isBehavioralActivity = false): array
    {
        if ($isBehavioralActivity) {
            $id = $this->getChargeServiceIdByCode($patient, ChargeableService::BHI);

            return [new ChargeableServiceDuration($id, $duration)];
        }

        if ($patient->isPcm()) {
            $id = $this->getChargeServiceIdByCode($patient, ChargeableService::PCM);

            return [new ChargeableServiceDuration($id, $duration)];
        }

        if ($patient->isCcm()) {
            if ( ! $patient->isCcmPlus()) {
                $id = $this->getChargeServiceIdByCode($patient, ChargeableService::CCM);

                return [new ChargeableServiceDuration($id, $duration)];
            }

            $currentTime = $patient->getCcmTime();
            $splitter    = new TimeSplitter();
            $slots       = $splitter->split($currentTime, $duration, false, false);

            $result = [];
            if ($slots->towards20) {
                $id       = $this->getChargeServiceIdByCode($patient, ChargeableService::CCM);
                $result[] = new ChargeableServiceDuration($id, $slots->towards20);
            }

            if ($slots->after20) {
                $id       = $this->getChargeServiceIdByCode($patient, ChargeableService::CCM_PLUS_40);
                $result[] = new ChargeableServiceDuration($id, $slots->after20);
            }

            if ($slots->after40) {
                $id       = $this->getChargeServiceIdByCode($patient, ChargeableService::CCM_PLUS_60);
                $result[] = new ChargeableServiceDuration($id, $slots->after40);
            }

            return $result;
        }
        
        $gcm = $this->getChargeServiceIdByCode($patient, ChargeableService::GENERAL_CARE_MANAGEMENT);
        if ($gcm) {
            $result[] = new ChargeableServiceDuration($gcm, $duration);
        }

        return [new ChargeableServiceDuration(null, $duration)];
    }
}
