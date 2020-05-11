<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportCcdas implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var array
     */
    private $ccdaIds;
    /**
     * @var int|null
     */
    private $initiatorUserId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $ccdaIds, int $initiatorUserId = null)
    {
        $this->ccdaIds         = $ccdaIds;
        $this->initiatorUserId = $initiatorUserId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);

        Ccda::whereIn('id', $this->ccdaIds)->chunkById(50, function ($ccdas) {
            $ccdas->each(function (Ccda $ccda) {
                $ccda->user_id = $this->initiatorUserId;
                ImportCcda::dispatch($ccda, true);
            });
        });
    }
}
