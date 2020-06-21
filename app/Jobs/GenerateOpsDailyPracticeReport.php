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
     * @var Carbon
     */
    private $fromDate;

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
        if ( ! $date) {
            $date = Carbon::now();
        }

        $this->date       = $date;
        $this->practiceId = $practiceId;

        //If the job was not run between 23:30-23:59 we need to get revisions from 23:30, 2 days before.
        //Example: we need data for 12/5 23:30 - 12/6 23:30 (time the report was supposed to run). If the job runs at 12/7 04:25,
        //we need fromDate = date->subDay(2)->setTimeFromTimeString('23:30')
        //
        //Even though this will make the report more accurate, it still makes the report not agree with the next day report (if that was ran at the designated time.
        //Example: if the report gets data for 12/5 23:30 - 12/7 02:30, the next day report will get data for 12/6 23:30 - 12/7 23:30.
        //Thus changes between 12/6 23:30 and 12/7 2:30 will be calculated in both reports, making (total added/lost patients and prior day totals have potential discrepancies)
        if ($this->date->gte($this->date->copy()->setTimeFromTimeString('00:00')) && $this->date->lte($this->date->copy()->setTimeFromTimeString('23:29'))) {
            $this->fromDate = $this->date->copy()->subDay(2)->setTimeFromTimeString('23:30');
        } else {
            $this->fromDate = $this->date->copy()->subDay()->setTimeFromTimeString('23:30');
        }
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
            ->opsDashboardQuery($this->date->copy()->startOfMonth(), $this->fromDate)
            ->findOrFail($this->practiceId);

        $report = OpsDashboardPracticeReport::firstOrCreate([
            'practice_id' => $practice->id,
            'date'        => $this->date->toDateString(),
        ]);

        $array = OpsDashboardReport::generate($practice, $this->date);

        //row can be null -> if practice has no enrolled patients and no added or remove, exclude from total report.
        //check exists on GenerateOpsDailyReport
        //however since since it's still an active practice, still log report in db as processed
        //deal with null reports
        $report->data         = $array;
        $report->is_processed = true;
        $report->save();
    }
}
