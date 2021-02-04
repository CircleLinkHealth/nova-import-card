<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\Core\Helpers\StringHelpers;
use CircleLinkHealth\Customer\AppConfig\CarePlanAutoApprover;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Rules\PatientIsUnique;
use CircleLinkHealth\Eligibility\CcdaImporter\ImportEnrollee;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportAndApproveEnrollee implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int   $enrolleeId;

    public function __construct(int $enrolleeId)
    {
        $this->enrolleeId = $enrolleeId;
    }

    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle()
    {
        if ( ! isset($this->enrolleeId) || empty($this->enrolleeId)) {
            return;
        }
        $enrollee = Enrollee::with('user.patientInfo')->find($this->enrolleeId);

        if ( ! $enrollee) {
            return;
        }
        if ( ! $enrollee->user) {
            $this->searchForExistingUser($enrollee);
        }
        
        $resolver = new DuplicatePatientResolver($enrollee);
        if ($resolver->hasDuplicateUsers()) {
            $resolver->resoveDuplicatePatients($enrollee->user_id, ...$resolver->duplicateUserIds());
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

        //If enrollee is from uploaded CSV from Nova Page,
        //Where we create Enrollees without any other data,
        //so we can consent them and then ask the practice to send us the CCDs
        //It is expected to reach this point, do not throw error
        if (Enrollee::UPLOADED_CSV === $enrollee->source && ! $enrollee->user->patientInfo) {
            return;
        }

        if ( ! $enrollee->user->patientInfo) {
            return;
        }

        $enrollee->user->patientInfo->ccm_status = Patient::ENROLLED;
        $enrollee->status                        = Enrollee::ENROLLED;

        if ($enrollee->isDirty()) {
            $enrollee->save();
        }
        if ($enrollee->user->patientInfo->isDirty()) {
            $enrollee->user->patientInfo->save();
        }
    }

    private function searchByFakeClhEmail(Enrollee $enrollee)
    {
        if (Str::startsWith($enrollee->email, 'eJ_') && Str::endsWith($enrollee->email, '@cpm.com')) {
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
