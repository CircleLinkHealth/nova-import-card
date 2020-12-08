<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Traits;

use App\Jobs\ProcessSelfEnrolablesFromCollectionJob;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use App\Services\Enrollment\EnrollmentInvitationService;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

trait ProcessSelfEnrollablesFromCollectionTrait
{
    public function decideActionOnUnresponsivePatient(User $user)
    {
        $service  = app(EnrollmentInvitationService::class);
        $enrollee = $user->enrollee;
        if ( ! empty($enrollee->care_ambassador_user_id)) {
            return;
        }

        if ($service->isUnreachablePatient($user)) {
            return;
        }

        if ( ! $user->loginEvents()->exists()) {
            $enrollee->update([
                'enrollment_non_responsive' => true,
                'auto_enrollment_triggered' => true,
            ]);
        }

        $enrollee->update(
            [
                'status'                    => Enrollee::TO_CALL,
                'auto_enrollment_triggered' => true,
                'requested_callback'        => now()->addDays(UnreachablesFinalAction::TO_CALL_AFTER_DAYS_HAVE_PASSED),
            ]
        );
    }
    /**
     * @return \CircleLinkHealth\Customer\Entities\CarePerson[]|\Illuminate\Database\Eloquent\Collection
     */
    public function careTeamProviderThatWasSetForEnrolle(User $patientUser, int $providerUserId)
    {
        return $patientUser->careTeamMembers
            ->where('type', ProcessSelfEnrolablesFromCollectionJob::PROVIDER_TYPE)
            ->where('member_user_id', $providerUserId);
    }
    /**
     * @return mixed
     */
    public function updateWrongCareTeamProvider(CarePerson $wrongProviderAsCareTeamMember, int $correctProviderId)
    {
        return $wrongProviderAsCareTeamMember
            ->update([
                'member_user_id' => $correctProviderId,
            ]);
    }
}
