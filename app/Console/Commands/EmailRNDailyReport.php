<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\SendDailyReportToRN;
use CircleLinkHealth\Customer\Services\NursesPerformanceReportService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class EmailRNDailyReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails to nurses containing a report on their performance for a given date.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurses:emailDailyReport {date? : Date to generate report for in YYYY-MM-DD.} {nurseUserIds? : Comma separated user IDs of nurses to email report to.} ';

    private $service;

    /**
     * Create a new command instance.
     */
    public function __construct(NursesPerformanceReportService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     * @throws \CircleLinkHealth\Core\Exceptions\FileNotFoundException
     *
     * @return mixed
     */
    public function handle()
    {
        $userIds = $this->argument('nurseUserIds') ?? null;

        $date = $this->argument('date') ?? Carbon::yesterday();

        if ( ! is_a($date, Carbon::class)) {
            $date = Carbon::parse($date);
        }

        $report = $this->service->showDataFromS3($date);

        if ($report->isEmpty()) {
            \Artisan::call(NursesPerformanceDailyReport::class);
            $report = $this->service->showDataFromS3($date);
        }

        if ($report->isEmpty()) {
            $this->error('No data found for '.$date->toDateString());

            return;
        }

        $counter      = 0;
        $emailsSent   = [];
        $dataNotFound = [];

        User::ofType('care-center')
            ->when(
                null != $userIds,
                function ($q) use ($userIds) {
                    $userIds = explode(',', $userIds);
                    $q->whereIn('id', $userIds);
                }
            )
            ->whereHas(
                'nurseInfo',
                function ($info) {
                    $info->where('status', 'active');
                }
            )
            ->chunk(
                50,
                function ($nurses) use (&$counter, &$emailsSent, $date, $report, &$dataNotFound) {
                    foreach ($nurses as $nurseUser) {
                        $this->warn("Processing $nurseUser->id");

                        $reportDataForNurse = $report->where('nurse_id', $nurseUser->id)->first();

                        if ( ! is_array($reportDataForNurse)) {
                            array_push($dataNotFound, $nurseUser->id);
                            continue;
                        }

                        SendDailyReportToRN::dispatch($nurseUser, $date, $reportDataForNurse);

                        $this->warn("Notified $nurseUser->id");

                        $emailsSent[] = [
                            'nurse' => $nurseUser->getFullName(),
                            'email' => $nurseUser->email,
                        ];

                        ++$counter;
                    }
                }
            );

        $this->info($date->toDateString());

        $this->table(
            [
                'nurse',
                'email',
            ],
            $emailsSent
        );

        $this->info("${counter} email(s) sent.");

        if ( ! empty($dataNotFound)) {
            $imploded = implode(', ', $dataNotFound);
            $message  = "Missing  report for date {$date->toDateString()} nurses with IDs: $imploded";
            $this->info($message);
            \Log::alert($message);
        }
    }
}
