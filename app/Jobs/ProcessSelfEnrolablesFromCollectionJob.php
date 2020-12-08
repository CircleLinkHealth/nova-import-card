<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Console\Commands\UpdateEnrolleeProvidersThatCreatedWrong;
use App\Search\ProviderByName;
use App\SelfEnrollment\Services\ProcessSelfEnrolablesWithCcdas;
use App\SelfEnrollment\Services\ProcessSelfEnrolablesWithNoCcdas;
use App\SelfEnrollment\Traits\ProcessSelfEnrollablesFromCollectionTrait;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessSelfEnrolablesFromCollectionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use ProcessSelfEnrollablesFromCollectionTrait;
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
            $enrolleeIds         = $enrolleeIds->toArray();
            $correctProviderUser = ProviderByName::first($providerName);

            if ( ! $correctProviderUser) {
                $class = ProviderByName::class;
                Log::warning("Provider [$providerName] not found using $class");
                $correctProviderUser = User::where('display_name', $providerName)->first();
            }

            if ( ! $correctProviderUser) {
                Log::channel('database')->critical("Provider [$providerName] not found in CPM");

                return;
            }

            foreach ($enrolleeIds as $enrolleeId) {
                /** @var User $patientUser */
                $patientUser = User::with('enrollee', 'careTeamMembers', 'enrollmentInvitationLinks', 'ccdas')
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

                $wrongProviderUserId = $wrongProviderUser->id;

                $ccdasToUpdate = Ccda::where('patient_id', $patientUser->id)
                    ->where('billing_provider_id', $wrongProviderUserId)
                    ->pluck('id');

                if ($ccdasToUpdate->isNotEmpty()) {
                    app(ProcessSelfEnrolablesWithCcdas::class)
                        ->process($patientUser, $ccdasToUpdate, $wrongProviderUserId, $correctProviderUser->id);
                    
                }else{
                    app(ProcessSelfEnrolablesWithNoCcdas::class)
                        ->process($patientUser, $wrongProviderUser->id, $correctProviderUser->id);
                }

                
                
            }
        }
    }
}
