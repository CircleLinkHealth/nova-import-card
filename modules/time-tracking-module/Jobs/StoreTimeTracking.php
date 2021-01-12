<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Jobs;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Jobs\ProcessMonthltyPatientTime;
use CircleLinkHealth\Customer\Jobs\ProcessNurseMonthlyLogs;
use CircleLinkHealth\SharedModels\DTO\CreatePageTimerParams;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use CircleLinkHealth\SharedModels\Services\PageTimerService;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\ParameterBag;

class StoreTimeTracking implements ShouldQueue, ShouldBeEncrypted
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
    private PageTimerService $pageTimerService;

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
        $this->activityService  = app(ActivityService::class);
        $this->pageTimerService = app(PageTimerService::class);

        /** @var User $provider */
        $provider = User::findOrFail($this->params->get('providerId', null));

        $patientId = $this->params->get('patientId', 0);
        $patient   = $this->getPatient($patientId);

        foreach ($this->params->get('activities', []) as $activity) {
            $pageTimer = $this->createPageTimer($activity);

            if ($this->isBillableActivity($pageTimer, $activity, $provider) && ! is_null($patient)) {
                $this->processBillableActivity($patient, $pageTimer, $activity['chargeable_service_id'] ?? -1);
            }

            if ($this->isProcessableCareAmbassadorActivity($activity, $provider)) {
                \CircleLinkHealth\Customer\Jobs\ProcessCareAmbassadorTime::dispatchNow($provider->id, $activity);
            }
        }
    }

    public function retryUntil(): \DateTime
    {
        return now()->addWeek();
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

    private function createPageTimer(array $activity): PageTimer
    {
        $params = (new CreatePageTimerParams())
            ->setActivity($activity)
            ->setIpAddr($this->params->get('ipAddr', null))
            ->setPatientId($this->params->get('patientId', null))
            ->setProgramId($this->params->get('programId', null))
            ->setProviderId($this->params->get('providerId', null))
            ->setUserAgent($this->params->get('userAgent', null))
            ->setRedirectLocation($this->params->get('redirectLocation', null));

        return $this->pageTimerService->createPageTimer($params);
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
    private function processBillableActivity(User $patient, PageTimer $pageTimer, int $chargeableServiceId = -1): void
    {
        $chargeableServiceDuration = $this->activityService->getChargeableServiceIdDuration($patient, $pageTimer->duration, $chargeableServiceId);
        $activity                  = app(PatientServiceProcessorRepository::class)->createActivityForChargeableService('pagetimer', $pageTimer, $chargeableServiceDuration);

        if ( ! $chargeableServiceDuration->id && $patient->chargeableServices()->count() > 0) {
            sendSlackMessage('#time-tracking-issues', "Could not assign activity[{$activity->id}] to chargeable service. Original csId[{$chargeableServiceId}]. See page timer entry {$pageTimer->id}. Patient[$patient->id].");
        }

        ProcessMonthltyPatientTime::dispatchNow($patient->id);
        ProcessNurseMonthlyLogs::dispatchNow($activity);
        event(new PatientActivityCreated($patient->id));
    }
}
