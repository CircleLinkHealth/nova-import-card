<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class FixPrimaryCare360 extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some Primary Care 360 patients do not have the correct providers';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:primary-care-360';

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
        Enrollee::wherePracticeId(353)
            ->whereNull('provider_id')
            ->with('ccda')
            ->whereHas('ccda')
            ->each(
                function ($enrollee) {
                        $this->warn("Start Enrollee[$enrollee->id]");

                        $ccd = $enrollee->ccda;
                        $eJ = $ccd->createEligibilityJobFromMedicalRecord();

                        $providerName = $enrollee->referring_provider_name = $ccd->referring_provider_name = $eJ->data['referring_provider_name'];
                        $provider = CcdaImporterWrapper::mysqlMatchProvider($providerName, $enrollee->practice_id);

                        if ( ! $provider) {
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

        return 0;
    }
}
