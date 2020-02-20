<?php

namespace App\Listeners;

use App\Events\CarePlanWasApproved;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangeOrApproveCareplanResponseListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     *
     * @param  object $event
     *
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if ($this->shouldBail($event->directMailMessage->from)) {
            return;
        }
    
        $careplanId = $this->getCareplanIdToChange($event->directMailMessage->body);
        if ($careplanId && $this->actionIsAuthorized($event->directMailMessage->from, $careplanId)) {
        
            return;
        }
        
        $careplanId = $this->getCareplanIdToApprove($event->directMailMessage->body);
        if ($careplanId && $this->actionIsAuthorized($event->directMailMessage->from, $careplanId)) {
            event(new CarePlanWasApproved(CarePlan::has('patient')->with('patient')->findOrFail($careplanId)->patient));
        }
    }
    
    private function shouldBail(string $sender)
    {
        return ! str_contains($sender, '@upg.ssdirect.aprima.com');
    }
    
    public function getCareplanIdToChange(string $body)
    {
        return $this->extractCarePlanId($body, '#change');
    }
    
    public function getCareplanIdToApprove(string $body)
    {
        return $this->extractCarePlanId($body, '#approve');
    }
    
    private function extractCarePlanId(string $body, string $key): ?int
    {
        preg_match("/$key\s*([\d]+)/", $body, $matches);
        
        if (array_key_exists(1, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }
    
    /**
     * @param string $from
     * @param int $careplanId
     *
     * @return bool
     */
    public function actionIsAuthorized(string $from, int $careplanId)
    {
        return CarePerson::joinWhere('care_plans', 'care_plans.user_id', '=', 'patient_care_team_members.user_id')->joinWhere(
            'emr_direct_addresses',
            'emr_direct_addresses.emrDirectable_id',
            '=',
            'patient_care_team_members.member_user_id'
        )->where('patient_care_team_members.type', CarePerson::BILLING_PROVIDER)->where(
            'care_plans.id',
            $careplanId
        )->where('emr_direct_addresses.address', $from)->where(
            'emr_direct_addresses.emrDirectable_type',
            User::class
        )->exists();
    }
}
