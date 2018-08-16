<?php

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
    private $reportData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $date = Carbon::now();

        $this->reportData = NurseDailyReport::data()->map(function ($nurseReport) use ($date) {

            $fullName = explode(' ', $nurseReport['name']);
            $first    = $fullName[0];
            $last     = $fullName[1];

            $nurse = User::with('nurseInfo.workhourables')
                         ->where([
                             ['first_name','=', $first],
                             ['last_name', '=', $last],
                         ])
                         ->first();

            $pageTimers = PageTimer::where('provider_id', $nurse->id)
                                   ->select(['id', 'duration', 'created_at'])
                                   ->where(function ($q) use ($date) {
                                       $q->where('created_at', '>=', $date->copy()->startOfDay())
                                         ->where('created_at', '<=', $date->copy()->endOfDay());
                                   })
                                   ->get()
                                   ->sum('duration');

            $offlineActivities = Activity::where('provider_id', $nurse->id)
                                         ->select(['id', 'duration', 'created_at'])
                                         ->where(function ($q) use ($date) {
                                             $q->where('created_at', '>=', $date->copy()->startOfDay())
                                               ->where('created_at', '<=', $date->copy()->endOfDay());
                                         })
                                         ->where('logged_from', 'manual_input')
                                         ->get()
                                         ->sum('duration');

            $total             = $pageTimers + $offlineActivities;
            $actualHours       = round($total / 3600, 1);
            $hoursCommitted = 'N/A';
            if ($nurse->nurseInfo){
                if ($nurse->nurseInfo->workhourables->count() > 0) {
                    $hoursCommitted = $nurse->nurseInfo->workhourables->first()->{strtolower($date->day)};
                }

            }


            return [
                'name'                     => $nurseReport['name'],
                'Time Since Last Activity' => $nurseReport['Time Since Last Activity'],
                '# Successful Calls Today' => $nurseReport['# Successful Calls Today'],
                '# Scheduled Calls Today'  => $nurseReport['# Scheduled Calls Today'],
                '# Completed Calls Today'  => $nurseReport['# Completed Calls Today'],
                'CCM Mins Today'           => $nurseReport['CCM Mins Today'],
                'last_activity'            => $nurseReport['last_activity'],
                'Actual Hours worked'      => $actualHours ?: 'N/A',
                'Hours Committed'          => $hoursCommitted,
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
