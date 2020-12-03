<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Search\ProviderByName;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use App\Services\Enrollment\EnrollmentInvitationService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UpdateEnrolleeFromCollectionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    use SerializesModels;

    private Collection $dataToUpdate;
    private int $practiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $dataToUpdate, int $practiceId)
    {
        $this->dataToUpdate = $dataToUpdate;
        $this->practiceId   = $practiceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->dataToUpdate as $providerName => $enrolleeIds) {
            $enrolleeIds  = $enrolleeIds->toArray();
            $providerUser = ProviderByName::first($providerName);

            if ( ! $providerUser) {
                $class = ProviderByName::class;
                Log::warning("Provider [$providerName] not found using $class");
                $providerUser = User::where('display_name', $providerName)->first();
            }

            if ( ! $providerUser) {
                Log::channel('database')->critical("Provider [$providerName] not found in CPM");

                return;
            }

            $this->updateEnrolleesProvider($enrolleeIds, $providerUser->id);
            foreach ($enrolleeIds as $enrolleeId) {
                /** @var User $patientUser */
                $patientUser = User::with('enrollee', 'careTeamMembers')
                    ->whereHas('enrollee', function ($enrollee) use ($enrolleeId) {
                        $enrollee->where('id', $enrolleeId);
                    })
                    ->where('program_id', $this->practiceId)
                    ->first();
    
                if ( ! $patientUser) {
                    Log::error("Enrollee with id [$enrolleeId] not found!");
        
                    return;
                }
                
                if ($patientUser->careTeamMembers->where('member_user_id', $providerUser->id)->isEmpty()) {
                    Log::channel('database')
                        ->critical("The correct provider to update [$providerUser->id] does not match careTeamMembers [member_user_id] of
                        enrolee user_id $patientUser->id");
                    return;
                }

                $this->decideActionOnUnresponsivePatient($patientUser);
            }
        }
    }

    private function decideActionOnUnresponsivePatient(User $user)
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

    private function updateEnrolleesProvider(array $enrolleeIds, int $providerUserId)
    {
        Enrollee::with('user.careTeamMembers')
            ->whereHas('user.careTeamMembers', function ($carePerson) use ($providerUserId) {
                $carePerson->where('member_user_id', $providerUserId);
            })
            ->whereIn('id', $enrolleeIds)->update([
                'provider_id' => $providerUserId,
            ]);
    }
}
