<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreTimeTracking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        //
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;
        
        $data = $request->input();
    
        $patientId  = $request->input('patientId');
        $providerId = $data['providerId'] ?? null;
    
        foreach ($data['activities'] as $activity) {
            $duration = $activity['duration'];
        
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $activity['start_time']);
            $endTime   = $startTime->copy()->addSeconds($duration);
        
            $redirectTo = $data['redirectLocation'] ?? null;
        
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
            $newActivity->program_id        = $data['programId'];
            $newActivity->ip_addr           = $data['ipAddr'];
            $newActivity->activity_type     = $activity['name'];
            $newActivity->title             = $activity['title'];
            $newActivity->user_agent        = $request->userAgent();
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
}
