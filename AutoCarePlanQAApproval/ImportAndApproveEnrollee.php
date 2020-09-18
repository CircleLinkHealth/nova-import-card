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
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
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

        if ( ! $enrollee->user) {
            $this->searchForExistingUser($enrollee);
        }
        if ( ! $enrollee->user) {
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

    private function searchForExistingUser(Enrollee &$enrollee)
    {
        $user = User::ofType(['participant', 'survey-only'])
            ->with('carePlan')
            ->whereHas('patientInfo', function ($q) use ($enrollee) {
                $q->where('mrn_number', $enrollee->mrn)
                    ->where('birth_date', $enrollee->dob);
            })->first();

        if ($user
            && StringHelpers::areSameStringsIfYouCompareOnlyLetters($user->first_name.$user->last_name, $enrollee->first_name.$enrollee->last_name)
        ) {
            $enrollee->user_id = $user->id;
            $enrollee->setRelation('user', $user);
        }
    }
}
