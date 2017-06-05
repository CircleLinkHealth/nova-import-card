<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCcda;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueCcdasToProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccda:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue CCDAs to process. Processing includes converting to json and saving the mrn, ccda date and referring provider name on the ccda.';

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
        $ccdas = Ccda::where('status', '=', Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)
            ->whereNull('mrn')
            ->take(200)
            ->get()
            ->map(function ($ccda) {
                $job = (new ProcessCcda($ccda))
                    ->onQueue('ccda-processor')
                    ->delay(Carbon::now()->addSeconds(30));

                dispatch($job);
            });
    }
}
