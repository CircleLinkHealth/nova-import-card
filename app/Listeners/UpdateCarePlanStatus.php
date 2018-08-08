<?php

namespace App\Listeners;

use App\CarePlan;
use App\Contracts\Efax;
use App\Events\CarePlanWasApproved;
use App\Events\PdfableCreated;
use App\Observers\PatientObserver;
use App\User;
use Carbon\Carbon;

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

            $date     = Carbon::now();
            $approver = auth()->user();

            $user->carePlanStatus               = CarePlan::PROVIDER_APPROVED;
            $user->carePlanProviderApprover     = $approver->id;
            $user->carePlanProviderApproverDate = $date->format('Y-m-d H:i:s');
            $user->carePlan->forward();
            event(new PdfableCreated($user->carePlan));

            if (app()->environment('worker') || app()->environment('production')) {

                $providers = [];
                $careplans = CarePlan::with('providerApproverUser')
                                     ->where('provider_date', '>=', $date->copy()->startOfDay())
                                     ->get()->map(function ($careplan) {

                        if ($careplan->providerApproverUser()) {
                            $providers[] = $careplan->providerApproverUser()->full_name;
                        }
                        return $careplan;
                    });
                $doctors   = implode(',', $providers);

                sendSlackMessage('#callcenter_ops',
                    "Dr.{$approver->full_name} approved {$user->id}'s care plan.\n");

                sendSlackMessage('#callcenter_ops',
                    "{$careplans->count()} Care Plans have been approved today by the following doctors: {$doctors}. \n
                    {$careplans->where('first_printed', null)->count()} Approved Care Plans have not yet been printed.\n");
            }


        } //This CarePlan is being `QA approved` by CLH
        elseif ($user->carePlanStatus == CarePlan::DRAFT
                && auth()->user()->hasPermissionForSite('care-plan-qa-approve', $user->primary_practice_id)) {
            $user->carePlan->status         = CarePlan::QA_APPROVED;
            $user->carePlan->qa_approver_id = auth()->id();
            $user->carePlan->save();

            if ((boolean)$practiceSettings->auto_approve_careplans) {
                $user->carePlan->status               = CarePlan::PROVIDER_APPROVED;
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
        if ( ! $user->notes->isEmpty()) {
            return;
        }

        (new PatientObserver())->sendPatientConsentedNote($user->patientInfo);
    }
}
