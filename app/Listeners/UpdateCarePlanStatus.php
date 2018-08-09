<?php

namespace App\Listeners;

use App\CarePlan;
use App\Contracts\Efax;
use App\Events\CarePlanWasApproved;
use App\Events\PdfableCreated;
use App\Observers\PatientObserver;
use App\User;

class UpdateCarePlanStatus
{
    /**
     * @var Efax
     */
    private $efax;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Efax $efax)
    {
        $this->efax = $efax;
    }

    /**
     * Handle the event.
     *
     * @param  CarePlanWasApproved $event
     *
     * @return void
     */
    public function handle(CarePlanWasApproved $event)
    {
        $user = $event->patient;

        //Stop the propagation to other Listeners if the CarePlan is already approved.
        if ($user->carePlanStatus == CarePlan::PROVIDER_APPROVED) {
            return false;
        }

        $practiceSettings = $event->practiceSettings;

        //This CarePlan has already been `QA approved` by CLH, and is now being approved by a member of the practice
        if ($user->carePlanStatus == CarePlan::QA_APPROVED && auth()->user()->canApproveCarePlans()) {
            $user->carePlanStatus               = CarePlan::PROVIDER_APPROVED;
            $user->carePlanProviderApprover     = auth()->user()->id;
            $user->carePlanProviderApproverDate = date('Y-m-d H:i:s');

            $user->carePlan->forward();

            event(new PdfableCreated($user->carePlan));
        } //This CarePlan is being `QA approved` by CLH
        elseif ($user->carePlanStatus == CarePlan::DRAFT
                && auth()->user()->hasPermissionForSite('care-plan-qa-approve', $user->primary_practice_id)) {
            $user->carePlan->status         = CarePlan::QA_APPROVED;
            $user->carePlan->qa_approver_id = auth()->id();
            $user->carePlan->save();

            if ((boolean)$practiceSettings->auto_approve_careplans) {
                $user->carePlan->status               = CarePlan::PROVIDER_APPROVED;
                $user->carePlan->provider_approver_id = optional($user->billingProviderUser())->id;
                $user->carePlan->save();

                event(new PdfableCreated($user->carePlan));
            }

            $this->addPatientConsentedNote($user);

            $user->carePlanQaDate = date('Y-m-d H:i:s'); // careplan_qa_date
        }

        $user->save();
    }

    /**
     * Send patient consented note to practice only after CLH has approved CarePlan.
     *
     * @param User $user
     */
    private function addPatientConsentedNote(User $user)
    {
        if ( ! $user->notes->isEmpty()) {
            return;
        }

        (new PatientObserver())->sendPatientConsentedNote($user->patientInfo);
    }
}
