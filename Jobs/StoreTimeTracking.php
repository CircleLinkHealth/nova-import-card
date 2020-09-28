<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Jobs;

use App\Jobs\ProcessCareAmbassadorTime;
use App\Jobs\ProcessMonthltyPatientTime;
use App\Jobs\ProcessNurseMonthlyLogs;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
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
    
    private ActivityService $activityService;
    
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
        $this->activityService = app(ActivityService::class);
        
        /** @var User $provider */
        $provider = User::findOrFail($this->params->get('providerId', null));
        
        $patientId = $this->params->get('patientId', 0);
        $patient   = $this->getPatient($patientId);
        
        foreach ($this->params->get('activities', []) as $activity) {
            $isBehavioral = isset($activity['is_behavioral'])
                ? (bool) $activity['is_behavioral'] && $this->isPatientBhi($patient)
                : false;
            
            $pageTimer = $this->createPageTimer($activity);
            
            if ($this->isBillableActivity($pageTimer, $activity, $provider) && ! is_null($patient)) {
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
    
    private function getPatient($patientUserId): ?User
    {
        if (empty($patientUserId)) {
            return null;
        }
        
        return User::without(['roles', 'perms'])
            ->with([
                'ccdProblems.cpmProblem',
                'primaryPractice.chargeableServices',
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
        $chargeableServicesDuration = $this->activityService->separateDurationForEachChargeableServiceId($patient, $pageTimer->duration, $isBehavioral);
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
            event(new PatientActivityCreated($patient->id));
        }
    }
}
