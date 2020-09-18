<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\AppConfig\DMDomainForAutoApproval;
use CircleLinkHealth\SharedModels\Entities\Call;
use App\DirectMailMessage;
use App\Events\CarePlanWasApproved;
use App\Note;
use App\Notifications\CarePlanDMApprovalConfirmation;
use App\Services\Calls\SchedulerService;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ChangeOrApproveCareplanResponseListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
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

    public function getCareplanIdToApprove(string $body)
    {
        return $this->extractCarePlanId($body, 'approve');
    }

    /**
     * Returns the CarePlan ID the provider requested changes for, or null if the provider did not request changes, or
     * the CarePlan ID was not found.
     *
     * @return int|null
     */
    public function getCareplanIdToChange(string $body)
    {
        return $this->extractCarePlanId($body, 'change');
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if ($this->shouldBail($event->directMailMessage)) {
            return;
        }

        if ( ! $this->attemptChange($event->directMailMessage)) {
            $this->attemptApproval($event->directMailMessage);
        }
    }

    /**
     * Approve the CarePlan, if the message contains code #approve.
     */
    private function attemptApproval(DirectMailMessage $directMailMessage): bool
    {
        $careplanId = $this->getCareplanIdToApprove($directMailMessage->body);
        if ($careplanId && $this->actionIsAuthorized($directMailMessage->from, $careplanId) && DirectMailMessage::DIRECTION_RECEIVED === $directMailMessage->direction) {
            $cp = $this->getCarePlan($careplanId);
            event(new CarePlanWasApproved($cp->patient, $cp->patient->billingProviderUser()));
            $cp->patient->billingProviderUser()->notify(new CarePlanDMApprovalConfirmation($cp->patient));

            return true;
        }

        return false;
    }

    /**
     * Create a Task(Call) with the body of the DM for Nurse to make changes to the CarePlan, if the message contains
     * code #change.
     */
    private function attemptChange(DirectMailMessage $directMailMessage): bool
    {
        $careplanId = $this->getCareplanIdToChange($directMailMessage->body);
        if ($careplanId && $this->actionIsAuthorized($directMailMessage->from, $careplanId) && DirectMailMessage::DIRECTION_RECEIVED === $directMailMessage->direction) {
            $cp   = $this->getCarePlan($careplanId);
            $note = Note::create(
                [
                    'patient_id' => $cp->user_id,
                    'author_id'  => $cp->patient->billingProviderUser()->id,
                    'type'       => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                    'body'       => $directMailMessage->body,
                ]
            );

            $newCallArgs = [
                'note_id'        => $note->id,
                'type'           => SchedulerService::TASK_TYPE,
                'sub_type'       => SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE,
                'service'        => 'phone',
                'status'         => 'scheduled',
                'asap'           => true,
                'attempt_note'   => $directMailMessage->body,
                'scheduler'      => $cp->patient->billingProviderUser()->id,
                'inbound_cpm_id' => $cp->user_id,
            ];

            if ($nurse = app(NurseFinderEloquentRepository::class)->find($cp->patient->id)) {
                $newCallArgs['outbound_cpm_id'] = $nurse->id;
            }

            $task = Call::create(
                $newCallArgs
            );

            return true;
        }

        return false;
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
     * Fetch the CarePlan with relations from the DB.
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
     * Returns true if this listener should not run, and fals if it should run.
     */
    private function shouldBail(DirectMailMessage $dm): bool
    {
        if (DirectMailMessage::DIRECTION_SENT === $dm->direction) {
            return true;
        }

        if ( ! DMDomainForAutoApproval::isEnabledForDomain($dm->from)) {
            return true;
        }

        return false;
    }
}
