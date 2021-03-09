<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;

use CircleLinkHealth\Eligibility\Jobs\ProcessEligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Console\Command;

class ProcessNextEligibilityBatchChunk extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process an eligibility batch of CCDAs.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:process';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($batch = $this->getNextBatch()) {
            ProcessEligibilityBatch::dispatch($batch->id);
            $this->line("Scheduled command to process batch:$batch->id");

            return;
        }

        $this->warn('No batches pending processing. Did not schedule anything.');
    }

    private function getNextBatch(): ?EligibilityBatch
    {
        return EligibilityBatch::where('status', '<', 2)
            ->orderByDesc('created_at')
            ->with('practice')
            ->first();
    }
}
