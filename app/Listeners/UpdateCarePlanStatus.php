<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Contracts\Efax;
use App\Events\CarePlanWasApproved;
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
     */
    public function __construct(Efax $efax)
    {
        $this->efax = $efax;
    }

    /**
     * Handle the event.
     */
    public function handle(CarePlanWasApproved $event)
    {
        $user = $event->patient;

        //Stop the propagation to other Listeners if the CarePlan is already approved.
        if (CarePlan::PROVIDER_APPROVED == $user->getCarePlanStatus()) {
            Log::debug('UpdateCarePlanStatus: Called but care plan is already approved. Exiting.');

            return;
        }
        $practiceSettings = $event->practiceSettings;
        //This CarePlan has already been `QA approved` by CLH, and is now being approved by a member of the practice
        if (CarePlan::QA_APPROVED == $user->getCarePlanStatus() && auth()->user()->canApproveCarePlans()) {
            Log::debug('UpdateCarePlanStatus: Ready to set status to PROVIDER_APPROVED');

            $date     = Carbon::now();
            $approver = auth()->user();

            $user->setCarePlanStatus(CarePlan::PROVIDER_APPROVED);
            $user->setCarePlanProviderApprover($approver->id);
            $user->setCarePlanProviderApproverDate($date->format('Y-m-d H:i:s'));
            $user->carePlan->forward();
            event(new PdfableCreated($user->carePlan));

            if (isProductionEnv()) {
                sendSlackMessage(
                    '#careplanprintstatus',
                    "Dr.{$approver->getFullName()} approved {$user->id}'s care plan.\n"
                );
            }
        } //This CarePlan is being `QA approved` by CLH
        elseif (CarePlan::DRAFT == $user->getCarePlanStatus()
                && auth()->user()->hasPermissionForSite('care-plan-qa-approve', $user->getPrimaryPracticeId())) {
            $user->carePlan->status         = CarePlan::QA_APPROVED;
            $user->carePlan->qa_approver_id = auth()->id();
            $user->carePlan->save();

            if ((bool) $practiceSettings->auto_approve_careplans || $user->patientIsUPG0506()) {
                $user->carePlan->status               = CarePlan::PROVIDER_APPROVED;
                $user->carePlan->provider_approver_id = optional($user->billingProviderUser())->id;

                if ($user->patientIsUPG0506()) {
                    $user->carePlan->provider_date = Carbon::now();
                }

                $user->carePlan->save();

                if ((bool) $practiceSettings->auto_approve_careplans) {
                    event(new PdfableCreated($user->carePlan));
                }
            }

            $this->addPatientConsentedNote($user);

            $user->setCarePlanQADate(date('Y-m-d H:i:s')); // careplan_qa_date
        }

        $user->carePlan->notifyPatientOfApproval();

        $user->save();
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
}
