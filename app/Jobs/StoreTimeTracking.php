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

    // Do not count time for these routes
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
     *
     * @param ParameterBag $params
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
        $provider = User::with('nurseInfo')
            ->findOrFail($this->params->get('providerId', null));

        $isPatientBhi = User::isBhiChargeable()
            ->where('id', $this->params->get('patientId'))
            ->exists();

        foreach ($this->params->get('activities', []) as $activity) {
            $isBehavioral = isset($activity['is_behavioral'])
                ? (bool) $activity['is_behavioral'] && $isPatientBhi
                : $isPatientBhi;

            $pageTimer = $this->createPageTimer($activity);

            if ($this->isBillableActivity($pageTimer, $provider)) {
                $newActivity = $this->createActivity($pageTimer, $isBehavioral);
                ProcessMonthltyPatientTime::dispatchNow($this->params->get('patientId'));
                ProcessNurseMonthlyLogs::dispatchNow($newActivity);
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
        return ['storetime', 'patient:'.$this->params->get('patientId'), 'provider:'.$this->params->get('providerId', null)];
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
        $pageTimer->provider_id       = $this->params->get('providerId', null);
        $pageTimer->start_time        = $startTime->toDateTimeString();
        $pageTimer->end_time          = $endTime->toDateTimeString();
        $pageTimer->url_full          = $activity['url'];
        $pageTimer->url_short         = $activity['url_short'];
        $pageTimer->program_id        = $this->params->get('programId', null);
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
    private function isBillableActivity(PageTimer $pageTimer, User $provider = null)
    {
        return ! ( ! $provider
                   || ! (bool) $provider->isCCMCountable()
                   || 0 == $pageTimer->patient_id
                   || in_array($pageTimer->title, self::UNTRACKED_ROUTES));
    }
}
