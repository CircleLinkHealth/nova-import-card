<?php

namespace App\Console\Commands;

use App\Jobs\DetermineCcdaEnrollmentEligibility;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Console\Command;
use Maknz\Slack\Facades\Slack;

class QueueCcdaToDetermineEnrollmentEligibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccda:determineEligibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Determine whether a patient is eligible to receive an enrollment call using CCDAs.';

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
     * @return mixed
     */
    public function handle()
    {
        $ccdas = Ccda::where([
            ['status', '=', Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY],
        ])->whereNotNull('mrn')->take(5000)->get(['id', 'referring_provider_name'])
            ->map(function ($ccda) {
                $job = (new DetermineCcdaEnrollmentEligibility($ccda))
                    ->onQueue('ccda-processor')
                    ->delay(Carbon::now()->addSeconds(20));

                dispatch($job);
            });
    }
}
