<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Jobs\CheckCcdaEnrollmentEligibility;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class QueueCcdaToDetermineEnrollmentEligibility extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Determine whether a patient is eligible to receive an enrollment call using CCDAs.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccda:determineEligibility';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Ccda::with(['batch', 'practice'])->where(
            [
                ['status', '=', Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY],
            ]
        )->whereNotNull('practice_id')->whereNotNull('batch_id')->chunkById(
            50,
            function ($ccdas) {
                foreach ($ccdas as $ccda) {
                    if ( ! $ccda->json) {
                        try {
                            $ccda->bluebuttonJson();
                        } catch (\Exception $exception) {
                            \Log::error($exception->getMessage());

                            continue;
                        }
                    }

                    CheckCcdaEnrollmentEligibility::dispatch($ccda, $ccda->practice, $ccda->batch)
                        ->onQueue('low');
                }
            }
        );
    }
}
