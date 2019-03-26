<?php

namespace CircleLinkHealth\TimeTracking\Entities;

use CircleLinkHealth\TimeTracking\Entities\Activity;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Services\ActivityService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

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
    
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
    
    public function durationInMinutes()
    {
        return $this->duration_seconds / 60;
    }
    
    public function getStatusCssClass()
    {
        switch ($this->is_approved) {
            case null:
                return 'warning';
            case 1:
                return 'success';
            case 0:
                return 'danger';
        }
    }
    
    public function status()
    {
        switch ($this->is_approved) {
            case null:
                return 'PENDING';
            case 1:
                return 'APPROVED';
            case 0:
                return 'REJECTED';
        }
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
            $res       = $client->put(
                $url,
                [
                    'form_params' => [
                        'startTime' => $this->duration_seconds,
                        $timeParam  => $this->duration_seconds,
                    ],
                ]
            );
            $status    = $res->getStatusCode();
            $body      = $res->getBody();
            if (200 == $status) {
                Log::info($body);
            } else {
                Log::critical($body);
            }
        } catch (\Exception $ex) {
            Log::critical($ex);
        }
        
        
        $activity = Activity::create(
            [
                'type'          => $this->type,
                'duration'      => $this->duration_seconds,
                'duration_unit' => 'seconds',
                'patient_id'    => $this->patient_id,
                'provider_id'   => $this->requester_id,
                'logger_id'     => auth()->id(),
                
                'is_behavioral' => $this->is_behavioral,
                'logged_from'   => 'manual_input',
                'performed_at'  => $this->performed_at->toDateTimeString(),
            ]
        );
        
        $nurse = optional($this->requester)->nurseInfo;
        
        (app(ActivityService::class))->processMonthlyActivityTime($this->patient_id, $this->performed_at);
        
        if ($nurse) {
            $computer = new AlternativeCareTimePayableCalculator($nurse);
            $computer->adjustNursePayForActivity($activity);
        }
        
        $this->is_approved = true;
        $this->activity_id = $activity->id;
        $this->save();
    }
    
    public function reject()
    {
        $this->is_approved = false;
        $this->save();
    }
}
