<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Services;

use App\SelfEnrollment\Traits\ProcessSelfEnrollablesFromCollectionTrait;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessSelfEnrolablesWithCcdas
{
    use ProcessSelfEnrollablesFromCollectionTrait;

    /**
     * @param Collection $ccdasToUpdate
     */
    public function process(User $patientUser, Collection $ccdasForUpdateIds, int $wrongProviderUserId, int $correctProviderUserId)
    {
        $enrollee   = $patientUser->enrollee;
        $enrolleeId = $enrollee->id;

        $this->updateCcdasProviderId($patientUser, $ccdasForUpdateIds->toArray(), $correctProviderUserId);
        $wrongProviderAsCareTeamMember = $this->careTeamProviderThatWasSetForEnrolle($patientUser, $wrongProviderUserId);
        $updatedCareTeamMembers        = $this->updateWrongCareTeamProvider($wrongProviderAsCareTeamMember->first(), $correctProviderUserId);
        if ( ! $updatedCareTeamMembers) {
            Log::channel('database')
                ->critical("Failed to update member_user_id for user_id [$patientUser->id] in care_team_members (with CCDA)");

            return;
        }
        $updatedEnrollee = $enrollee->update([
            'provider_id' => $correctProviderUserId,
        ]);

        if ( ! $updatedEnrollee) {
            Log::channel('database')->critical("Failed to update provider_id for id [$enrolleeId] in Enrollees. (With CCDA)");

            return;
        }

        $this->decideActionOnUnresponsivePatient($patientUser);
    }

    /**
     * @param int $wrongProviderId
     */
    private function updateCcdasProviderId(User $patientUser, array $ccdaIdsForUpdate, int $correctProviderId)
    {
        $patientUser->ccdas()->whereIn('id', $ccdaIdsForUpdate)->update([
            'billing_provider_id' => $correctProviderId,
        ]);
    }
}
