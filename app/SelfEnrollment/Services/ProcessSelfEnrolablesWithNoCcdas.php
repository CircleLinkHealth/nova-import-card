<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Services;

use App\Jobs\ProcessSelfEnrolablesFromCollectionJob;
use App\SelfEnrollment\Traits\ProcessSelfEnrollablesFromCollectionTrait;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Log;

class ProcessSelfEnrolablesWithNoCcdas
{
    use ProcessSelfEnrollablesFromCollectionTrait;

    public function process(User $patientUser, int $wrongProviderUserId, int $correctProviderUserId)
    {
        $enrollee                        = $patientUser->enrollee;
        $enrolleeId                      = $enrollee->id;
        $wrongProviderAsCareTeamMember   = $this->careTeamProviderThatWasSetForEnrolle($patientUser, $wrongProviderUserId);
        $correctProviderAsCareTeamMember = $this->careTeamProviderThatWasSetForEnrolle($patientUser, $correctProviderUserId);

        if ($correctProviderAsCareTeamMember->isEmpty()) { // Maybe For some reason is updated before.
            if ($wrongProviderAsCareTeamMember->isNotEmpty() && $patientUser->enrollee->provider_id === $wrongProviderUserId) { // It could a third provider for example.
                $updatedCareTeamMembers = $this->updateWrongCareTeamProvider($wrongProviderAsCareTeamMember->first(), $correctProviderUserId);
                if ( ! $updatedCareTeamMembers) {
                    Log::channel('database')
                        ->critical("Failed to update member_user_id for user_id [$patientUser->id] in care_team_members (NO CCDA)");

                    return;
                }

                $updatedEnrollee = $patientUser->enrollee->update([
                    'provider_id' => $correctProviderUserId,
                ]);

                if ( ! $updatedEnrollee) {
                    Log::channel('database')->critical("Failed to update provider_id for id [$enrolleeId] in Enrollees. (No CCDA)");

                    return;
                }
            } else {
                Log::error("Fishy! Expected Provider [user_id:$wrongProviderUserId
                        in [column:member_user_id table:patient_care_team_members] for [user_id:$patientUser->id]");

                return;
            }
        }

        $this->decideActionOnUnresponsivePatient($patientUser);
    }
}
