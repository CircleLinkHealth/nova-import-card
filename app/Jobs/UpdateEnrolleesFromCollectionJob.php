<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Console\Commands\UpdateEnrolleeProvidersThatCreatedWrong;
use App\Search\ProviderByName;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use App\Services\Enrollment\EnrollmentInvitationService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UpdateEnrolleesFromCollectionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SelfEnrollableTrait;

    use SerializesModels;

    const PROVIDER_TYPE = 'billing_provider';
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
        $wrongProviderEmail = UpdateEnrolleeProvidersThatCreatedWrong::WRONG_PROVIDER_EMAIL;
        $wrongProviderUser  = User::where('email', $wrongProviderEmail)->first();

        if ( ! $wrongProviderUser) {
            Log::error("Weird, known existing user in Production [email:$wrongProviderEmail] not found!");

            return;
        }

        foreach ($this->dataToUpdate as $providerName => $enrolleeIds) {
            $enrolleesToUpdateCount = $enrolleeIds->count();
            $enrolleeIds            = $enrolleeIds->toArray();
            $correctProviderUser    = ProviderByName::first($providerName);

            if ( ! $correctProviderUser) {
                $class = ProviderByName::class;
                Log::warning("Provider [$providerName] not found using $class");
                $correctProviderUser = User::where('display_name', $providerName)->first();
            }

            if ( ! $correctProviderUser) {
                Log::channel('database')->critical("Provider [$providerName] not found in CPM");

                return;
            }

            $enrolleeIdsThatGotDirty    = collect();
            $enrolleeIdsThatStayedClean = collect();
            $enrolleeIdsThatFailed      = collect();
            foreach ($enrolleeIds as $enrolleeId) {
                /** @var User $patientUser */
                $patientUser = User::with('enrollee', 'careTeamMembers', 'enrollmentInvitationLinks')
                    ->whereHas('enrollee', function ($enrollee) use ($enrolleeId) {
                        $enrollee->where('id', $enrolleeId)->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT);
                    })
                    ->where('program_id', $this->practiceId)
                    ->first();

                if ( ! $patientUser) {
                    Log::error("Enrollee with id [$enrolleeId] not found!");

                    return;
                }

                if ( ! $patientUser->enrollee->enrollmentInvitationLinks()->exists()) {
                    Log::channel('database')
                        ->critical("Enrollee with user_id [$patientUser->id] does not have an invitation link.");

                    return;
                }

                $wrongProviderAsCareTeamMember   = $this->providerIsSetAsCareTeamMember($patientUser, $wrongProviderUser->id);
                $correctProviderAsCareTeamMember = $this->providerIsSetAsCareTeamMember($patientUser, $correctProviderUser->id);

                if ($correctProviderAsCareTeamMember->isEmpty()) { // Maybe For some reason is updated before.
                    if ($wrongProviderAsCareTeamMember->isNotEmpty() && $patientUser->enrollee->provider_id === $wrongProviderUser->id) { // It could a third provider for example.
                        $updatedCareTeamMembers = $wrongProviderAsCareTeamMember->first()
                            ->update([
                                'member_user_id' => $correctProviderUser->id,
                            ]);

                        if ( ! $updatedCareTeamMembers) {
                            $enrolleeIdsThatFailed->push($enrolleeId);
                            Log::channel('database')
                                ->critical("Failed to update member_user_id for user_id [$patientUser->id] in care_team_members");

                            return;
                        }

                        $updatedEnrollee = $patientUser->enrollee->update([
                            'provider_id' => $correctProviderUser->id,
                        ]);

                        if ( ! $updatedEnrollee) {
                            $enrolleeIdsThatFailed->push($enrolleeId);
                            Log::channel('database')
                                ->critical("Failed to update provider_id for id [$enrolleeId] in Enrollees");

                            return;
                        }

                        $enrolleeIdsThatGotDirty->push($enrolleeId);
                    } else {
                        $enrolleeIdsThatFailed->push($enrolleeId);
                        Log::error("Fishy! Expected Provider [user_id:$wrongProviderUser->id
                        in [column:member_user_id table:patient_care_team_members] for [user_id:$patientUser->id]");

                        return;
                    }
                } else {
                    $enrolleeIdsThatStayedClean->push($enrolleeId);
                }

                $enrolleeIdsThatGotDirtyCount    = $enrolleeIdsThatGotDirty->count();
                $enrolleeIdsThatFailedCount      = $enrolleeIdsThatFailed->count();
                $enrolleeIdsThatStayedCleanCount = $enrolleeIdsThatStayedClean->count();

                Log::info("Updated wrong imported Providers in care team members: For PROVIDER $providerName: [updated $enrolleeIdsThatGotDirtyCount], [failed $enrolleeIdsThatFailedCount],
                [unprocessed $enrolleeIdsThatStayedCleanCount] FROM $enrolleesToUpdateCount entries");

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

    /**
     * @return \CircleLinkHealth\Customer\Entities\CarePerson[]|\Illuminate\Database\Eloquent\Collection
     */
    private function providerIsSetAsCareTeamMember(User $patientUser, int $providerUserId)
    {
        return $patientUser->careTeamMembers
            ->where('type', self::PROVIDER_TYPE)
            ->where('member_user_id', $providerUserId);
    }
}
