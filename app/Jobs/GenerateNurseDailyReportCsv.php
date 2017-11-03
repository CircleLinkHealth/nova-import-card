<?php

namespace App\Jobs;

use App\Reports\NurseDailyReport;
use App\Repositories\Cache\UserNotificationList;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class GenerateNurseDailyReportCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $reportData;
    private $notifyUserIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $notifyUserIds)
    {
        $this->notifyUserIds = $notifyUserIds;
        $this->reportData = NurseDailyReport::data()->map(function ($nurseReport) {
            return [
                $nurseReport['name'],
                $nurseReport['Time Since Last Activity'],
                $nurseReport['# Successful Calls Today'],
                $nurseReport['# Scheduled Calls Today'],
                $nurseReport['# Completed Calls Today'],
                $nurseReport['CCM Mins Today'],
                $nurseReport['last_activity'],
            ];
        });
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = $this->exportToCsv($this->reportData);

        $link = linkToDownloadFile("exports/{$path['file']}");

        $this->notifyUserIds->map(function ($userId) use ($link) {
            $userNotification = new UserNotificationList($userId);

            $userNotification->push('Nurse Daily Report', '', $link, 'Download Spreadsheet');
        });

    }

    /**
     * Exports the Patient List to a csv file.
     */
    public function exportToCsv()
    {
        $now = Carbon::now()->toDateTimeString();
        $filename = "Nurse_Daily_Report";

        return Excel::create("{$filename}_{$now}", function ($excel) {
            $excel->sheet('Nurse Daily Report', function ($sheet) {
                $sheet->fromArray(
                    $this->reportData->all()
                );
            });
        })->store('xls', false, true);
    }
}
