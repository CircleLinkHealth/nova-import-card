<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\Core\Helpers\StringHelpers;
use CircleLinkHealth\Customer\AppConfig\CarePlanAutoApprover;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\ImportEnrollee;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportAndApproveEnrollee implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Enrollee $enrollee;

    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
    }

    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle()
    {
        $enrollee = $this->enrollee;
    
        //If enrollee is from uploaded CSV from Nova Page,
        //Where we create Enrollees without any other data,
        //so we can consent them and then ask the practice to send us the CCDs
        //It is expected to reach this point, do not throw error
        if (Enrollee::UPLOADED_CSV === $enrollee->source) {
            return;
        }

        if ( ! $enrollee->user) {
            $this->searchForExistingUser($enrollee);
        }
        if ( ! $enrollee->user || ! $enrollee->user->isParticipant()) {
            ImportEnrollee::import($enrollee);
        }
        if ($enrollee->user && $enrollee->user->carePlan && in_array($enrollee->user->carePlan->status, [CarePlan::PROVIDER_APPROVED, CarePlan::QA_APPROVED, CarePlan::RN_APPROVED])) {
            $enrollee->status = Enrollee::ENROLLED;
            $enrollee->save();

            return;
        }
        if (is_null($enrollee->user)) {
            return;
        }

        $enrollee->loadMissing([
            'user.patientInfo',
        ]);

        if ($enrollee->user->isSurveyOnly()) {
            return;
        }

        ApproveIfValid::dispatch($enrollee->user, CarePlanAutoApprover::user());

        $enrollee->user->patientInfo->ccm_status = Patient::ENROLLED;
        $enrollee->status                        = Enrollee::ENROLLED;

        if ($enrollee->isDirty()) {
            $enrollee->save();
        }
        if ($enrollee->user->patientInfo->isDirty()) {
            $enrollee->user->patientInfo->save();
        }
    }

    public function retryUntil(): \DateTime
    {
        return now()->addDay();
    }

    private function searchByFakeClhEmail(Enrollee $enrollee)
    {
        if (starts_with($enrollee->email, 'eJ_') && ends_with($enrollee->email, '@cpm.com')) {
            $user = User::ofType(['participant', 'survey-only'])
                ->where('first_name', $enrollee->first_name)
                ->where('last_name', $enrollee->last_name)
                ->where('email', $enrollee->email)
                ->first();

            return $this->validateMatchAndAttachToEnrollee($enrollee, $user);
        }

        return null;
    }

    private function searchByMrnAndDOB(Enrollee &$enrollee): ?User
    {
        $user = User::ofType(['participant', 'survey-only'])
            ->with('carePlan')
            ->whereHas('patientInfo', function ($q) use ($enrollee) {
                $q->where('mrn_number', $enrollee->mrn)
                    ->where('birth_date', $enrollee->dob);
            })->first();

        return $this->validateMatchAndAttachToEnrollee($enrollee, $user);
    }

    private function searchForExistingUser(Enrollee &$enrollee)
    {
        if ( ! is_null($this->searchByMrnAndDOB($enrollee))) {
            return;
        }

        if ( ! is_null($this->searchByFakeClhEmail($enrollee))) {
            return;
        }
    }

    private function validateMatchAndAttachToEnrollee(Enrollee &$enrollee, ?User $user): ?User
    {
        if ($user
            && StringHelpers::partialOrFullNameMatch($user->first_name.$user->last_name, $enrollee->first_name.$enrollee->last_name)
        ) {
            $enrollee->user_id = $user->id;
            $enrollee->setRelation('user', $user);

            return $user;
        }

        return null;
    }
}
