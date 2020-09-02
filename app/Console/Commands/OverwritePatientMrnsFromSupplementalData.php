<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OverwritePatientMrnsFromSupplementalData extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Overwrite data for patients with importing hook to replace mrn. This is a safe check in case we missed any.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'overwrite:mrn_from_supplemental_data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \App\Jobs\OverwritePatientMrnsFromSupplementalData::dispatchNow();
    }
}
