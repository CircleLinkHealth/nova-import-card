<?php

namespace App\Jobs;

use CircleLinkHealth\TimeTracking\Entities\Activity;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use App\Services\ActivityService;
use CircleLinkHealth\Customer\Entities\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\HttpFoundation\ParameterBag;

class StoreTimeTracking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var array
     */
    protected $params;
    
    /**
     * Create a new job instance.
     *
     * @param array $params
     */
    public function __construct(ParameterBag $params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $patientId  = $this->params->get('patientId');
        $providerId = $this->params->get('providerId', null);
    
        foreach ($this->params->get('activities', []) as $activity) {
            $duration = $activity['duration'];
        
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $activity['start_time']);
            $endTime   = $startTime->copy()->addSeconds($duration);
        
            $redirectTo = $this->params->get('redirectLocation', null);
        
            $isBhi = User::isBhiChargeable()
                         ->where('id', $patientId)
                         ->exists();
        
            $newActivity                    = new PageTimer();
            $newActivity->redirect_to       = $redirectTo;
            $newActivity->billable_duration = $duration;
            $newActivity->duration          = $duration;
            $newActivity->duration_unit     = 'seconds';
            $newActivity->patient_id        = $patientId;
            $newActivity->provider_id       = $providerId;
            $newActivity->start_time        = $startTime->toDateTimeString();
            $newActivity->end_time          = $endTime->toDateTimeString();
            $is_behavioral                  = isset($activity['is_behavioral'])
                ? (bool)$activity['is_behavioral'] && $isBhi
                : $isBhi;
            $newActivity->url_full          = $activity['url'];
            $newActivity->url_short         = $activity['url_short'];
            $newActivity->program_id        = $this->params->get('programId');
            $newActivity->ip_addr           = $this->params->get('ipAddr');
            $newActivity->activity_type     = $activity['name'];
            $newActivity->title             = $activity['title'];
            $newActivity->user_agent        = $this->params->get('userAgent', null);
            $newActivity->save();
        
            $activityId = null;
        
            if ($newActivity->billable_duration > 0) {
                $activityId = $this->addPageTimerActivities($newActivity, $is_behavioral);
            }
        
            if ($activityId) {
                $this->handleNurseLogs($activityId);
            }
        }
    }
    
    private function addPageTimerActivities(PageTimer $pageTimer, $is_behavioral = false)
    {
        // check params to see if rule exists
        $params = [];
        
        //user
        $user = User::find($pageTimer->provider_id);
        
        if (( ! (bool)$user->isCCMCountable()) || (0 == $pageTimer->patient_id)) {
            return false;
        }
        
        // activity param
        $params['activity'] = $pageTimer->activity_type;
        
        $omitted_routes = [
            'patient.activity.create',
            'patient.activity.providerUIIndex',
            'patient.reports.progress',
        ];
        
        $is_ommited = in_array($pageTimer->title, $omitted_routes);
        
        if ( ! $is_ommited) {
            $activityParams                  = [];
            $activityParams['type']          = $params['activity'];
            $activityParams['provider_id']   = $pageTimer->provider_id;
            $activityParams['is_behavioral'] = $is_behavioral;
            $activityParams['performed_at']  = $pageTimer->start_time;
            $activityParams['duration']      = $pageTimer->billable_duration;
            $activityParams['duration_unit'] = 'seconds';
            $activityParams['patient_id']    = $pageTimer->patient_id;
            $activityParams['logged_from']   = 'pagetimer';
            $activityParams['logger_id']     = $pageTimer->provider_id;
            $activityParams['page_timer_id'] = $pageTimer->id;
            
            // if rule exists, create activity
            $activityId = Activity::createNewActivity($activityParams);
            
            app(ActivityService::class)->processMonthlyActivityTime([$pageTimer->patient_id]);
            
            $pageTimer->processed = 'Y';
            
            $pageTimer->save();
            
            return $activityId;
        }
        
        // update pagetimer
        $pageTimer->processed = 'Y';
        
        $pageTimer->save();
        
        return false;
    }
    
    private function handleNurseLogs($activityId)
    {
        $activity = Activity::with('patient.patientInfo')
                            ->find($activityId);
        
        if ( ! $activity) {
            return;
        }
        
        $nurse = Nurse::whereUserId($activity->provider_id)
                      ->first();
        
        if ( ! $nurse) {
            return;
        }
        
        $alternativePayComputer = new AlternativeCareTimePayableCalculator($nurse);
        $alternativePayComputer->adjustNursePayForActivity($activity);
    }
}
