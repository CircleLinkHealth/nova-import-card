<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        $provider = User::with(['nurseInfo'])
            ->findOrFail($this->params->get('providerId', null));

        $patientId = $this->params->get('patientId', 0);

        $isPatientBhi = ! empty($patientId) && User::isBhiChargeable()
            ->where('id', $patientId)
            ->exists();

        foreach ($this->params->get('activities', []) as $activity) {
            $isBehavioral = isset($activity['is_behavioral'])
                ? (bool) $activity['is_behavioral'] && $isPatientBhi
                : $isPatientBhi;

            $pageTimer = $this->createPageTimer($activity);

            if ($this->isBillableActivity($pageTimer, $activity, $provider)) {
                $newActivity = $this->createActivity($pageTimer, $isBehavioral);
                ProcessMonthltyPatientTime::dispatchNow($patientId);
                ProcessNurseMonthlyLogs::dispatchNow($newActivity);
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
     * Create an Activity.
     *
     * @param bool $isBehavioral
     *
     * @return Activity|\Illuminate\Database\Eloquent\Model
     */
    private function createActivity(PageTimer $pageTimer, $isBehavioral = false)
    {
        return Activity::create(
            [
                'type'          => $pageTimer->activity_type,
                'provider_id'   => $pageTimer->provider_id,
                'is_behavioral' => $isBehavioral,
                'performed_at'  => $pageTimer->start_time,
                'duration'      => $pageTimer->billable_duration,
                'duration_unit' => 'seconds',
                'patient_id'    => $pageTimer->patient_id,
                'logged_from'   => 'pagetimer',
                'logger_id'     => $pageTimer->provider_id,
                'page_timer_id' => $pageTimer->id,
            ]
        );
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

    /**
     * Returns true if an activity should be created, and false if it should not.
     *
     * @return bool
     */
    private function isBillableActivity(PageTimer $pageTimer, array $activity, User $provider = null)
    {
        return ! ( ! $provider
                   || ! (bool) $provider->isCCMCountable()
                   || 0 == $pageTimer->patient_id
                   || in_array($pageTimer->title, self::UNTRACKED_ROUTES)
                   || $activity['force_skip']);
    }

    /**
     * If user is a care ambassador, then we should process their time in CA logs.
     * Unless activity is marked in {@link UNTRACKED_CA_ACTIVITIES}.
     *
     * @return bool
     */
    private function isProcessableCareAmbassadorActivity(array $activity, User $provider = null)
    {
        return ! $activity['force_skip'] && $provider->isCareAmbassador();
    }
}
