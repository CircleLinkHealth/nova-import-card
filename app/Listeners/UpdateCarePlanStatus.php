<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Contracts\Efax;
use App\Events\CarePlanWasApproved;
use App\Events\CarePlanWasQAApproved;
use App\Events\PdfableCreated;
use App\Observers\PatientObserver;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Log;

class UpdateCarePlanStatus
{
    /**
     * @var Efax
     */
    private $efax;
    
    /**
     * Create the event listener.
     *
     * @param Efax $efax
     */
    public function __construct(Efax $efax)
    {
        $this->efax = $efax;
    }
    
    /**
     * Handle the event.
     *
     * @param CarePlanWasApproved $event
     */
    public function handle(CarePlanWasApproved $event)
    {
        //Stop the propagation to other Listeners if the CarePlan is already approved.
        if (CarePlan::PROVIDER_APPROVED == $event->patient->getCarePlanStatus()) {
            Log::debug('UpdateCarePlanStatus: Called but care plan is already approved. Exiting.');
            
            return;
        }
        
        $this->attemptApproval($event);
    }
    
    /**
     * Send patient consented note to practice only after CLH has approved CarePlan.
     */
    private function addPatientConsentedNote(User $user)
    {
        if ( ! $user->notes->isEmpty()) {
            return;
        }
        
        (new PatientObserver())->sendPatientConsentedNote($user->patientInfo);
    }
    
    private function providerApprove(CarePlanWasApproved &$event)
    {
        Log::debug('UpdateCarePlanStatus: Ready to set status to PROVIDER_APPROVED');
        
        $date     = Carbon::now();
        $approver = auth()->user();
        
        $event->patient->setCarePlanStatus(CarePlan::PROVIDER_APPROVED);
        $event->patient->setCarePlanProviderApprover($approver->id);
        $event->patient->setCarePlanProviderApproverDate($date->format('Y-m-d H:i:s'));
        $event->patient->carePlan->forward();
        event(new PdfableCreated($event->patient->carePlan));
        
        if (isProductionEnv()) {
            sendSlackMessage(
                '#careplanprintstatus',
                "Dr.{$approver->getFullName()} approved {$event->patient->id}'s care plan.\n"
            );
        }
    }
    
    private function qaApprove(CarePlanWasApproved &$event)
    {
        $event->patient->carePlan->status         = CarePlan::QA_APPROVED;
        $event->patient->carePlan->qa_approver_id = auth()->id();
        $event->patient->carePlan->save();
        
        event(new CarePlanWasQAApproved($event->patient->carePlan));
        
        $this->addPatientConsentedNote($event->patient);
        
        $event->patient->setCarePlanQADate(date('Y-m-d H:i:s'));
    }
    
    /**
     * This CarePlan has already been `QA approved` by CLH, and is now being approved by a member of the practice.  We can now store it with status `Provider Approved`
     *
     * @param CarePlanWasApproved $event
     *
     * @return bool
     */
    private function shouldBeProviderApproved(CarePlanWasApproved &$event): bool
    {
        return CarePlan::QA_APPROVED == $event->patient->getCarePlanStatus() && auth()->user()->canApproveCarePlans();
    }
    
    /**
     * This is a `Draft` CarePlan what was just `QA approved` by CLH. We can now store it with status `QA Approved`
     *
     * @param CarePlanWasApproved $event
     *
     * @return bool
     */
    private function shouldBeQAApproved(CarePlanWasApproved &$event): bool
    {
        return CarePlan::DRAFT == $event->patient->getCarePlanStatus()
               && auth()->user()->hasPermissionForSite('care-plan-qa-approve', $event->patient->getPrimaryPracticeId());
    }
    
    /**
     * Attempt to approve
     *
     * @param CarePlanWasApproved $event
     */
    private function attemptApproval(CarePlanWasApproved &$event)
    {
        if ($this->shouldBeProviderApproved($event)) {
            $this->providerApprove($event);
        }
        
        elseif ($this->shouldBeQAApproved($event)) {
            $this->qaApprove($event);
        }
    
        $event->patient->carePlan->notifyPatientOfApproval();
    
        $event->patient->save();
    }
}
