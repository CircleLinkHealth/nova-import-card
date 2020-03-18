<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\CarePlanWasApproved;
use App\Events\CarePlanWasProviderApproved;
use App\Events\CarePlanWasQAApproved;
use App\Events\PdfableCreated;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Log;

class UpdateCarePlanStatus
{
    /**
     * Handle the event.
     *
     * @param CarePlanWasApproved $event
     */
    public function handle(CarePlanWasApproved $event)
    {
        if ($this->carePlanIsAlreadyApproved($event->patient->carePlan)) {
            Log::debug('UpdateCarePlanStatus: Called but care plan is already approved. Exiting.');
            
            return;
        }
        
        $this->attemptApproval($event->patient, $event->approver);
    }
    
    /**
     * @param User $patient
     * @param User $approver
     */
    private function providerApprove(User &$patient, User $approver)
    {
        Log::debug('UpdateCarePlanStatus: Ready to set status to PROVIDER_APPROVED');
    
        $patient->carePlan->status         = CarePlan::PROVIDER_APPROVED;
        $patient->carePlan->provider_approver_id = $approver->id;
        $patient->carePlan->provider_date        = now()->toDateTimeString();
        $patient->carePlan->save();
        
        //@todo: refactor to laravel notification via slack channel
        if (isProductionEnv()) {
            sendSlackMessage(
                '#careplanprintstatus',
                "Dr.{$approver->getFullName()} approved {$patient->id}'s care plan.\n"
            );
        }
    }
    
    /**
     * @param User $patient
     * @param User $approver
     */
    private function qaApprove(User &$patient, User $approver)
    {
        $patient->carePlan->status         = CarePlan::QA_APPROVED;
        $patient->carePlan->qa_approver_id = $approver->id;
        $patient->carePlan->qa_date        = now()->toDateTimeString();
        $patient->carePlan->save();
    }
    
    /**
     * This CarePlan has already been `QA approved` by CLH, and is now being approved by a member of the practice.  We
     * can now store it with status `Provider Approved`
     *
     * @param User $patient
     *
     * @param User $approver
     *
     * @return bool
     */
    private function shouldBeProviderApproved(User $patient, User $approver): bool
    {
        return CarePlan::QA_APPROVED == $patient->getCarePlanStatus() && $approver->canApproveCarePlans();
    }
    
    /**
     * This is a `Draft` CarePlan what was just `QA approved` by CLH. We can now store it with status `QA Approved`
     *
     * @param User $patient
     *
     * @param User $approver
     *
     * @return bool
     */
    private function shouldBeQAApproved(User $patient, User $approver): bool
    {
        return CarePlan::DRAFT == $patient->getCarePlanStatus()
               && $approver->hasPermissionForSite('care-plan-qa-approve', $patient->getPrimaryPracticeId());
    }
    
    /**
     * Attempt to approve
     *
     * @param User $patient
     * @param User $approver
     */
    private function attemptApproval(User &$patient, User &$approver)
    {
        if ($this->shouldBeProviderApproved($patient, $approver)) {
            $this->providerApprove($patient, $approver);
        } elseif ($this->shouldBeQAApproved($patient, $approver)) {
            $this->qaApprove($patient, $approver);
        }
        
        $patient->save();
    
        $patient->carePlan->notifyPatientOfApproval();
    }
    
    private function carePlanIsAlreadyApproved(CarePlan $carePlan)
    {
        return CarePlan::PROVIDER_APPROVED == $carePlan->status;
    }
}
