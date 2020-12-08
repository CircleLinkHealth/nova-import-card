<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Console\Commands\UpdateEnrolleeProvidersThatCreatedWrong;
use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use App\SelfEnrollment\Traits\ProcessSelfEnrollablesFromCollectionTrait;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
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

        $correctProviderUser = CcdaImporterWrapper::mysqlMatchProvider($this->providerName, $this->practiceId) ?? null;

        foreach ($this->enrolleeIds as $enrolleeId) {
            $patientUser = $this->getUser($enrolleeId, $this->practiceId);

            if ( ! $patientUser) {
                Log::error("Enrollee with id [$enrolleeId] not found!");
                CreateSurveyOnlyUserFromEnrollee::dispatch(Enrollee::find($enrolleeId));
                $patientUser = $this->getUser($enrolleeId, $this->practiceId);

                continue;
            }

            $wrongProviderUserId   = $wrongProviderUser->id;
            $correctProviderUserId = optional($correctProviderUser)->id;

            $updated = Ccda::where(function ($ccda) use ($patientUser) {
                $ccda->where('patient_id', $patientUser->id)
                    ->orWhere('patient_mrn', $patientUser->enrollee->mrn);
            })->where('practice_id', $patientUser->program_id)
                ->whereNotNull('practice_id')
                ->where('billing_provider_id', $wrongProviderUserId)
                ->update([
                    'billing_provider_id' => $correctProviderUserId,
                    'patient_id'          => $patientUser->id,
                ]);

            if ($patientUser->getBillingProviderId() !== $correctProviderUserId) {
                $patientUser->setBillingProviderId($correctProviderUserId);

                $updatedEnrollee = $patientUser->enrollee->update([
                    'provider_id'             => $correctProviderUserId,
                    'referring_provider_name' => optional($correctProviderUser)->getFullName() ?? $this->providerName,
                ]);

                if ( ! $updatedEnrollee) {
                    Log::channel('database')->critical("Failed to update provider_id for id [$enrolleeId] in Enrollees. (No CCDA)");

                    return;
                }
            }

            $this->decideActionOnUnresponsivePatient($patientUser);
        }
    }

    private function getUser(int $enrolleeId, int $practiceId)
    {
        return User::with('enrollee', 'billingProvider', 'enrollmentInvitationLinks', 'ccdas')
            ->whereHas('enrollee', function ($enrollee) use ($enrolleeId) {
                $enrollee->where('id', $enrolleeId);
            })
            ->ofType(['participant', 'survey-only'])
            ->ofPractice($practiceId)
            ->first();
    }
}
