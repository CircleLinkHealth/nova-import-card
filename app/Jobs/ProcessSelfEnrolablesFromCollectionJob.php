<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Console\Commands\UpdateEnrolleeProvidersThatCreatedWrong;
use App\SelfEnrollment\Traits\ProcessSelfEnrollablesFromCollectionTrait;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
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
    private Collection $enrolleeIds;
    private int $practiceId;
    private string $providerName;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $enrolleeIds, int $practiceId, string $providerName)
    {
        $this->enrolleeIds  = $enrolleeIds;
        $this->practiceId   = $practiceId;
        $this->providerName = $providerName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $wrongProviderEmail = UpdateEnrolleeProvidersThatCreatedWrong::WRONG_PROVIDER_EMAIL;
        $wrongProviderUser  = User::where('email', $wrongProviderEmail)->firstOrFail();
        
        $correctProviderUser = CcdaImporterWrapper::mysqlMatchProvider($this->providerName, $this->practiceId);

        if ( ! $correctProviderUser) {
            throw new \Exception("Provider [$this->providerName] not found in CPM");
        }

        foreach ($this->enrolleeIds as $enrolleeId) {
            /** @var User $patientUser */
            $patientUser = User::with('enrollee', 'billingProvider', 'enrollmentInvitationLinks', 'ccdas')
                ->whereHas('enrollee', function ($enrollee) use ($enrolleeId) {
                    $enrollee->where('id', $enrolleeId);
                })
                ->ofType(['participant', 'survey-only'])
                ->ofPractice($this->practiceId)
                ->first();

            if ( ! $patientUser) {
                Log::error("Enrollee with id [$enrolleeId] not found!");

                continue;
            }

            $wrongProviderUserId       = $wrongProviderUser->id;
            $correctProviderUserId     = $correctProviderUser->id;

            Ccda::where(function($ccda) use($patientUser){
                $ccda->where('patient_id', $patientUser->id)
                    ->orWhere('patient_mrn', $patientUser->enrollee->mrn);
            })->where('practice_id', $patientUser->program_id)
                ->whereNotNull('practice_id')
                ->where('billing_provider_id', $wrongProviderUserId)
                ->update([
                'billing_provider_id' => $correctProviderUserId,
            ]);
            
            if ($patientUser->getBillingProviderId() !== $correctProviderUserId) {
                $patientUser->setBillingProviderId($correctProviderUserId);

                $updatedEnrollee = $patientUser->enrollee->update([
                    'provider_id' => $correctProviderUserId,
                ]);

                if ( ! $updatedEnrollee) {
                    Log::channel('database')->critical("Failed to update provider_id for id [$enrolleeId] in Enrollees. (No CCDA)");

                    return;
                }
            }

            $this->decideActionOnUnresponsivePatient($patientUser);
        }
    }
}
