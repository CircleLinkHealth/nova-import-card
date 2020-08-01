<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Jobs\ProcessCcda;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class QueueCcdasToProcess extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue CCDAs to process. Processing includes converting to json and saving the mrn, ccda date and referring provider name on the ccda.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccda:process';

    /**
     * Create a new command instance.
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
            ->inRandomOrder()
            ->limit(5)
            ->get(['id'])
            ->map(function ($ccda) {
                $job = (new ProcessCcda($ccda))
                    ->onQueue('low')
                    ->delay(Carbon::now()->addSeconds(20));

                dispatch($job);
            });
    }
}
