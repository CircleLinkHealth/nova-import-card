<?php

namespace App\Console\Commands;

use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\LGHDetermineCcdaEnrollmentEligibility;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
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
                     ->take(30)
                     ->get(['id', 'practice_id'])
                     ->map(function ($ccda) {
                         //lgh
                         if ($ccda->practice_id == 141) {
                             dispatch(
                                 (new LGHDetermineCcdaEnrollmentEligibility($ccda))
                                     ->delay(Carbon::now()->addSeconds(20))
                                     ->onQueue('ccda-processor')
                             );

                             return;
                         }


                         if ($ccda->practice_id) {
                             $practice = Practice::find($ccda->practice_id);

                             if (!$practice) return;

                             dispatch(
                                 (new CheckCcdaEnrollmentEligibility($ccda, $practice))
                                     ->delay(Carbon::now()->addSeconds(20))
                                     ->onQueue('ccda-processor')
                             );
                         }
                     });

        $this->output->success('Jobs scheduled!');
    }
}
