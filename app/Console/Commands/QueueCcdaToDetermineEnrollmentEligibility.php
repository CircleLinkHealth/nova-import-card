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
        Slack::to('#background-tasks')->send("Running ccda:determineEligibility.");


        $ccdas = Ccda::where([
            ['status', '=', Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY],
//            ['referring_provider_name', '=', 'Angela Snow'],
        ])->whereNotNull('mrn')->take(100)->get(['id', 'referring_provider_name'])
            //            ->whereIn('referring_provider_name', [
//                'Michael Alexander',
//                'Juan Perez',
//                'Bradley Chastant MD',
//                'Bradley Chastant',
//                'Angela Snow',
//            ])
            ->map(function ($ccda) {
//                $job = (new DetermineCcdaEnrollmentEligibility($ccda))->onQueue('ccda-determine-eligibility');
                dispatch(new DetermineCcdaEnrollmentEligibility($ccda));
            });
    }
}
