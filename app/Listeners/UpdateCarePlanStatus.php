<?php

namespace App\Listeners;

use App\CarePlan;
use App\Contracts\Efax;
use App\Events\CarePlanWasApproved;
use App\Events\PdfableCreated;
use App\Observers\PatientObserver;
use App\User;
use Carbon\Carbon;
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
        if ($user->getCarePlanStatus() == CarePlan::PROVIDER_APPROVED) {
            Log::debug('UpdateCarePlanStatus: Called but care plan is already approved. Exiting.');

            return;
        }
        $practiceSettings = $event->practiceSettings;
        //This CarePlan has already been `QA approved` by CLH, and is now being approved by a member of the practice
        if ($user->getCarePlanStatus() == CarePlan::QA_APPROVED && auth()->user()->canApproveCarePlans()) {

            Log::debug("UpdateCarePlanStatus: Ready to set status to PROVIDER_APPROVED");

            $date     = Carbon::now();
            $approver = auth()->user();

            $user->setCarePlanStatus(CarePlan::PROVIDER_APPROVED);
            $user->setCarePlanProviderApprover($approver->id);
            $user->setCarePlanProviderApproverDate($date->format('Y-m-d H:i:s'));
            $user->carePlan->forward();
            event(new PdfableCreated($user->carePlan));

            if (app()->environment(['worker', 'production', 'staging'])) {
                sendSlackMessage('#careplanprintstatus',
                    "Dr.{$approver->getFullName()} approved {$user->id}'s care plan.\n");
            }


        } //This CarePlan is being `QA approved` by CLH
        elseif ($user->getCarePlanStatus() == CarePlan::DRAFT
                && auth()->user()->hasPermissionForSite('care-plan-qa-approve', $user->getPrimaryPracticeId())) {
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

            $user->setCarePlanQADate(date('Y-m-d H:i:s')); // careplan_qa_date
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
