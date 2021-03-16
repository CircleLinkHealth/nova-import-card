<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class FixCommonwealth extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some Commonwealth patients do not have the correct providers';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:commonwealth';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Enrollee::wherePracticeId(232)
            ->where('status', '!=', Enrollee::ENROLLED)
            ->whereDoesntHave('user.patientInfo', function ($q) {
                $q->enrolled();
            })
            ->with('eligibilityJob.targetPatient.ccda')
            ->with('user')
            ->without('user.roles.perms')
            ->orderByDesc('id')
            ->each(
                function ($enrollee) {
                    $this->warn("Start Enrollee[$enrollee->id]");
                    $tP = $enrollee->eligibilityJob->targetPatient;
                    $checkable = app(AthenaEligibilityCheckableFactory::class)->makeAthenaEligibilityCheckable(
                        $tP
                    );
                    $eJ = $checkable->createAndProcessEligibilityJobFromMedicalRecord();
                    $ccd = $checkable->getMedicalRecord();
                    $providerName = $enrollee->referring_provider_name = $ccd->referring_provider_name = $eJ->data['referring_provider_name'];
                    $provider = CcdaImporterWrapper::mysqlMatchProvider($providerName, $enrollee->practice_id);

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
                        $this->line("Saving Enrollee[$enrollee->id]");
                    }
                },
                50
            );
    }
}
