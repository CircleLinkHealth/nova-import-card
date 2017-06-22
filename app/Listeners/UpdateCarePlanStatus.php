<?php

namespace App\Listeners;

use App\Events\CarePlanWasApproved;
use App\Events\PdfableCreated;
use App\Observers\PatientObserver;
use App\User;

class UpdateCarePlanStatus
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if ($user->carePlanStatus == 'provider_approved') {
            return false;
        }

        if ($user->carePlanStatus == 'qa_approved' && auth()->user()->can('care-plan-approve')) {
            $user->carePlanStatus = 'provider_approved'; // careplan_status
            $user->carePlanProviderApprover = auth()->user()->id; // careplan_provider_approver
            $user->carePlanProviderApproverDate = date('Y-m-d H:i:s'); // careplan_provider_date

            event(new PdfableCreated($user->carePlan));

        } elseif ($user->carePlanStatus == 'draft' && auth()->user()->can('care-plan-qa-approve')) {
            $user->carePlanStatus = 'qa_approved'; // careplan_status
            $user->carePlanQaApprover = auth()->user()->id; // careplan_qa_approver

            if ($user->carePlan->patient->primaryPractice->settings()->first()->auto_approve_careplans) {
                $user->carePlan->status = 'provider_approved';
                $user->carePlan->provider_approver_id = $user->billingProvider()->id ?? null;
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
        if (!$user->notes->isEmpty()) {
            return;
        }

        (new PatientObserver())->sendPatientConsentedNote($user->patientInfo);
    }
}
