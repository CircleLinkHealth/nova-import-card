<?php

namespace App\Jobs;

use App\Reports\NurseDailyReport;
use App\Repositories\Cache\UserView;
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
    private $cachedUserView;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $notifyUserIds)
    {
        $this->notifyUserIds = $notifyUserIds;
        $this->reportData = NurseDailyReport::data();
        $this->cachedUserView = new UserView($this->notifyUserIds);

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = $this->exportToCsv($this->reportData);

        $now = Carbon::now();

        $message = link_to_route('download', "Download Nurse Daily Report for {$now->toDateTimeString()}", [
            'filePath' => $path['full'],
        ]);

        $this->cachedUserView->storeSuccessResponse($message);
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
