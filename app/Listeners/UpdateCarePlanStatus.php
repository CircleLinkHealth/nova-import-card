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

        $practiceSettings = $user->carePlan->patient->primaryPractice->cpmSettings();

        if ($user->carePlanStatus == CarePlan::QA_APPROVED && auth()->user()->canApproveCarePlans()) {
            $user->carePlanStatus = CarePlan::PROVIDER_APPROVED; // careplan_status
            $user->carePlanProviderApprover = auth()->user()->id; // careplan_provider_approver
            $user->carePlanProviderApproverDate = date('Y-m-d H:i:s'); // careplan_provider_date

            if ((boolean)$practiceSettings->efax_pdf_careplan) {
                $this->efax->send($user->locations->first()->fax, $user->carePlan->toPdf());
            }

            event(new PdfableCreated($user->carePlan));
        } elseif ($user->carePlanStatus == CarePlan::DRAFT && auth()->user()->hasPermissionForSite('care-plan-qa-approve', $user->primary_practice_id)) {
            $user->carePlan->status = CarePlan::QA_APPROVED; // careplan_status
            $user->carePlan->qa_approver_id = auth()->id(); // careplan_qa_approver
            $user->carePlan->save();

            if ((boolean)$practiceSettings->auto_approve_careplans) {
                $user->carePlan->status = CarePlan::PROVIDER_APPROVED;
                $user->carePlan->provider_approver_id = $user->billingProviderUser()->id ?? null;
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
