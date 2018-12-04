<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Activity;
use App\PageTimer;
use App\Reports\NurseDailyReport;
use App\Services\Cache\NotificationService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class GenerateNurseDailyReportCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $date;
    private $reportData;

    /**
     * Create a new job instance.
     *
     * @param Carbon|null $forDate
     */
    public function __construct(Carbon $forDate = null)
    {
        $this->date = $forDate ?? Carbon::now();

        $this->reportData = NurseDailyReport::data($forDate)->map(function ($nurseReport) {
            $fullName = explode(' ', $nurseReport['name']);
            $first = $fullName[0];
            $last = $fullName[1];

            $nurse = User::ofType('care-center')
                ->with('nurseInfo.workhourables')
                ->where([
                    ['first_name', '=', $first],
                    ['last_name', '=', $last],
                ])
                ->first();

            if (!$nurse) {
                \Log::error("User not found: {$nurseReport['name']}");

                return [];
            }

            $pageTimers = PageTimer::where('provider_id', $nurse->id)
                ->select(['id', 'duration', 'created_at'])
                ->where(function ($q) {
                    $q->where('created_at', '>=', $this->date->copy()->startOfDay())
                        ->where('created_at', '<=', $this->date->copy()->endOfDay());
                })
                ->get()
                ->sum('duration');

            $offlineActivities = Activity::where('provider_id', $nurse->id)
                ->select(['id', 'duration', 'created_at'])
                ->where(function ($q) {
                    $q->where('created_at', '>=', $this->date->copy()->startOfDay())
                        ->where('created_at', '<=', $this->date->copy()->endOfDay());
                })
                ->where('logged_from', 'manual_input')
                ->get()
                ->sum('duration');

            $total = $pageTimers + $offlineActivities;
            $actualHours = round($total / 3600, 1);

            $hoursCommitted = 'N/A';
            if ($nurse->nurseInfo->workhourables && $nurse->nurseInfo->workhourables->count() > 0) {
                $hoursCommitted = $nurse->nurseInfo->workhourables->first()->{strtolower($this->date->format('l'))};
            }

            return [
                'name'                     => $nurseReport['name'],
                'Time Since Last Activity' => $nurseReport['Time Since Last Activity'],
                '# Successful Calls Today' => $nurseReport['# Successful Calls Today'],
                '# Scheduled Calls Today'  => $nurseReport['# Scheduled Calls Today'],
                '# Completed Calls Today'  => $nurseReport['# Completed Calls Today'],
                'CCM Mins Today'           => $nurseReport['CCM Mins Today'],
                'last_activity'            => $nurseReport['last_activity'],
                'Actual Hours worked'      => $actualHours
                    ?: 'N/A',
                'Hours Committed' => 0 == $hoursCommitted
                    ? '0'
                    : $hoursCommitted,
            ];
        })->filter()->values();
    }

    /**
     * Exports the Patient List to a csv file.
     */
    public function exportToCsv()
    {
        $dateString = $this->date->toDateTimeString();
        $filename   = 'Nurse_Daily_Report';

        return Excel::create("{$filename}_{$dateString}", function ($excel) {
            $excel->sheet('Nurse Daily Report', function ($sheet) {
                $sheet->fromArray(
                    $this->reportData->all()
                );
            });
        })->store('csv', false, true);
    }

    /**
     * Execute the job.
     *
     * @param NotificationService $notificationService
     *
     * @throws \Exception
     */
    public function handle(NotificationService $notificationService)
    {
        $path = $this->exportToCsv($this->reportData);

        $media = User::find(357)
            ->addMedia($path['full'])
            ->toMediaCollection("nurse_daily_report_for_{$this->date->toDateString()}");

        $link = $media->getUrl();

        $notificationService->notifyAdmins('Nurse Daily Report '.$this->date->toDateString(), '', $link, 'Download Spreadsheet');
    }
}
