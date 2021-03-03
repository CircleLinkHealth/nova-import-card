<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\Enrollee;

class FixCommonwealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:commonwealth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some Commonwealth patients do not have the correct providers';

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
                ->whereStatus(Enrollee::QUEUE_AUTO_ENROLLMENT)
                ->where('auto_enrollment_triggered', 0)
                ->whereNull('user_id')
                ->with('eligibilityJob.targetPatient.ccda')
                ->each(
                    function ($enrollee) {
                        $this->warn("Start Enrollee[$enrollee->id]");
                        $tP           = $enrollee->eligibilityJob->targetPatient;
                        $checkable    = app(AthenaEligibilityCheckableFactory::class)->makeAthenaEligibilityCheckable(
                            $tP
                        );
                        $eJ           = $checkable->createAndProcessEligibilityJobFromMedicalRecord();
                        $ccd          = $checkable->getMedicalRecord();
                        $providerName = $enrollee->referring_provider_name = $ccd->referring_provider_name = $eJ->data['referring_provider_name'];
                        $provider     = CcdaImporterWrapper::mysqlMatchProvider($providerName, $enrollee->practice_id);
                        
                        if (! $provider) {
                            return;
                        }
            
                        $ccd->billing_provider_id = $enrollee->provider_id = $provider->id;
            
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
                    }
                );
    }
}
