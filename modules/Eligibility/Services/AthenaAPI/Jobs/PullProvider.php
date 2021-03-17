<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Services\AthenaAPI\Jobs;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullProvider implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $enrolleeId;

    public function __construct(int $enrolleeId)
    {
        $this->enrolleeId = $enrolleeId;
    }

    public function handle()
    {
        $enrollee = Enrollee::where('id', $this->enrolleeId)
            ->with('eligibilityJob.targetPatient.ccda')
            ->has('eligibilityJob.targetPatient.ccda')
            ->with('user')
            ->without('user.roles.perms')
            ->first();

        if ( ! $enrollee) {
            return;
        }
        $tP        = $enrollee->eligibilityJob->targetPatient;
        $checkable = app(AthenaEligibilityCheckableFactory::class)->makeAthenaEligibilityCheckable(
            $tP
        );
        $eJ           = $checkable->createAndProcessEligibilityJobFromMedicalRecord();
        $ccd          = $checkable->getMedicalRecord();
        $providerName = $enrollee->referring_provider_name = $ccd->referring_provider_name = $eJ->data['referring_provider_name'];
        $provider     = CcdaImporterWrapper::mysqlMatchProvider($providerName, $enrollee->practice_id);

        if ( ! $provider) {
            return;
        }

        $ccd->billing_provider_id = $enrollee->provider_id = $provider->id;

        if ($enrollee->user) {
            $enrollee->user->setBillingProviderId($provider->id);
        }

        if ($ccd->isDirty()) {
            $ccd->save();
        }
        if ($eJ->isDirty()) {
            $eJ->save();
        }
        if ($enrollee->isDirty()) {
            $enrollee->save();
        }
    }
}
