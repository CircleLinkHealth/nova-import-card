<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Services\OpsDashboardReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateOpsDailyPracticeReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const MEMORY_LIMIT = '800M';

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var Practice
     */
    private $practiceId;

    /**
     * Create a new job instance.
     *
     * @param mixed $practiceId
     */
    public function __construct($practiceId, Carbon $date = null)
    {
        $this->date       = $date ?: Carbon::now();
        $this->practiceId = $practiceId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        ini_set('memory_limit', self::MEMORY_LIMIT);
        ini_set('max_input_time', $this->timeout);
        ini_set('max_execution_time', $this->timeout);

        $practice = Practice::select(['id', 'display_name'])
            ->activeBillable()
            ->opsDashboardQuery($this->date->copy()->startOfMonth())
            ->findOrFail($this->practiceId);

        OpsDashboardPracticeReport::updateOrCreate(
            [
                'practice_id' => $practice->id,
                'date'        => $this->date->toDateString(),
            ],
            [
                'data'         => OpsDashboardReport::generate($practice, $this->date),
                'is_processed' => true,
            ]
        );
    }
}
