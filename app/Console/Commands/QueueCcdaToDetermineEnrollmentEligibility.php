<?php

namespace App\Console\Commands;

use App\Jobs\LGHDetermineCcdaEnrollmentEligibility;
use App\Jobs\OttawaDetermineCcdaEnrollmentEligibility;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
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
        ])->whereNotNull('mrn')
            ->inRandomOrder()
            ->take(5000)
            ->get(['id', 'practice_id'])
            ->map(function ($ccda) {
                //lgh
                if ($ccda->practice_id == 141) {
                    $job = (new LGHDetermineCcdaEnrollmentEligibility($ccda))
                        ->delay(Carbon::now()->addSeconds(20));

                    dispatch($job);
                }

                //ottawa
                if ($ccda->practice_id == 158) {
                    $job = (new OttawaDetermineCcdaEnrollmentEligibility($ccda))
                        ->delay(Carbon::now()->addSeconds(20));

                    dispatch($job);
                }
            });

        $this->output->success('Jobs scheduled!');
    }
}
