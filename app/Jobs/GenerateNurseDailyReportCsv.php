<?php

namespace App\Jobs;

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
    private $reportData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reportData = NurseDailyReport::data()->map(function ($nurseReport) {
            return [
                'name'                     => $nurseReport['name'],
                'Time Since Last Activity' => $nurseReport['Time Since Last Activity'],
                '# Successful Calls Today' => $nurseReport['# Successful Calls Today'],
                '# Scheduled Calls Today'  => $nurseReport['# Scheduled Calls Today'],
                '# Completed Calls Today'  => $nurseReport['# Completed Calls Today'],
                'CCM Mins Today'           => $nurseReport['CCM Mins Today'],
                'last_activity'            => $nurseReport['last_activity'],
            ];
        });
    }

    /**
     * Execute the job.
     *
     * @param NotificationService $notificationService
     *
     * @return void
     * @throws \Exception
     */
    public function handle(NotificationService $notificationService)
    {
        $path = $this->exportToCsv($this->reportData);

        $date = Carbon::now();

        $media = User::find(357)
                     ->addMedia($path['full'])
                     ->toMediaCollection("nurse_daily_report_for_{$date->toDateString()}");

        $link = $media->getUrl();

        $notificationService->notifyAdmins('Nurse Daily Report', '', $link, 'Download Spreadsheet');
    }

    /**
     * Exports the Patient List to a csv file.
     */
    public function exportToCsv()
    {
        $now      = Carbon::now()->toDateTimeString();
        $filename = "Nurse_Daily_Report";

        return Excel::create("{$filename}_{$now}", function ($excel) {
            $excel->sheet('Nurse Daily Report', function ($sheet) {
                $sheet->fromArray(
                    $this->reportData->all()
                );
            });
        })->store('csv', false, true);
    }
}
