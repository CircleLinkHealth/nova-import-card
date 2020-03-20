<?php

namespace App\Listeners;

use App\Call;
use App\DirectMailMessage;
use App\Events\CarePlanWasApproved;
use App\Note;
use App\Notifications\CarePlanDMApprovalConfirmation;
use App\Services\Calls\SchedulerService;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\AppConfig\DMDomainForAutoApproval;
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
     * @param object $event
     *
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if ($this->shouldBail($event->directMailMessage->from)) {
            return;
        }
        
        if ( ! $this->attemptChange($event->directMailMessage)) {
            $this->attemptApproval($event->directMailMessage);
        }
    }
    
    /**
     * Returns true if this listener should not run, and fals if it should run.
     *
     * @param string $sender
     *
     * @return bool
     */
    private function shouldBail(string $sender): bool
    {
        return ! DMDomainForAutoApproval::isEnabledForDomain($sender);
    }
    
    /**
     * Returns the CarePlan ID the provider requested changes for, or null if the provider did not request changes, or
     * the CarePlan ID was not found.
     *
     * @param string $body
     *
     * @return int|null
     */
    public function getCareplanIdToChange(string $body)
    {
        return $this->extractCarePlanId($body, 'change');
    }
    
    
    public function getCareplanIdToApprove(string $body)
    {
        return $this->extractCarePlanId($body, 'approve');
    }
    
    private function extractCarePlanId(string $body, string $key): ?int
    {
        preg_match("/#\s*$key\s*([\d]+)/", $body, $matches);
        
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
        return CarePerson::join('care_plans', 'care_plans.user_id', '=', 'patient_care_team_members.user_id')->join(
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
    
    /**
     * Create a Task(Call) with the body of the DM for Nurse to make changes to the CarePlan, if the message contains
     * code #change.
     *
     * @param DirectMailMessage $directMailMessage
     *
     * @return bool
     */
    private function attemptChange(DirectMailMessage $directMailMessage): bool
    {
        $careplanId = $this->getCareplanIdToChange($directMailMessage->body);
        if ($careplanId && $this->actionIsAuthorized($directMailMessage->from, $careplanId)) {
            $cp   = $this->getCarePlan($careplanId);
            $note = Note::create(
                [
                    'patient_id' => $cp->user_id,
                    'author_id'  => $cp->patient->billingProviderUser()->id,
                    'type'       => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                    'body'       => $directMailMessage->body,
                ]
            );
            
            $task = Call::create(
                [
                    'note_id'         => $note->id,
                    'type'            => 'task',
                    'sub_type'        => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                    'service'         => 'phone',
                    'status'          => 'scheduled',
                    'asap'            => true,
                    'attempt_note'    => $directMailMessage->body,
                    'scheduler'       => $cp->patient->billingProviderUser()->id,
                    'inbound_cpm_id'  => $cp->user_id,
                    'outbound_cpm_id' => $cp->patient->patientInfo->getNurse(),
                ]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Fetch the CarePlan with relations from the DB.
     *
     * @param int $careplanId
     *
     * @return CarePlan|CarePlan[]|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    private function getCarePlan(int $careplanId)
    {
        return CarePlan::has('patient.billingProvider')->has('patient.patientInfo')->with(
            ['patient.billingProvider', 'patient.patientInfo']
        )->findOrFail($careplanId);
    }
    
    /**
     * Approve the CarePlan, if the message contains code #approve.
     *
     * @param DirectMailMessage $directMailMessage
     *
     * @return bool
     */
    private function attemptApproval(DirectMailMessage $directMailMessage): bool
    {
        $careplanId = $this->getCareplanIdToApprove($directMailMessage->body);
        if ($careplanId && $this->actionIsAuthorized($directMailMessage->from, $careplanId)) {
            $cp = $this->getCarePlan($careplanId);
            event(new CarePlanWasApproved($cp->patient, $cp->patient->billingProviderUser()));
            $cp->patient->billingProviderUser()->notify(new CarePlanDMApprovalConfirmation($cp->patient));
            
            return true;
        }
        
        return false;
    }
}
