<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Exports\NurseDailyReport;
use App\Notifications\NurseDailyReportGenerated;
use App\Services\Cache\NotificationService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateNurseDailyReportCsv implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $date;
    private $reportData;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $forDate = null)
    {
        $this->date = $forDate ?? Carbon::now();
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle(NotificationService $notificationService)
    {
        $media = (new NurseDailyReport($this->date))->storeAndAttachMediaTo(SaasAccount::whereSlug('circlelink-health')->firstOrFail());

        $link = $media->getUrl();

        $notificationService->notifyAdmins(
            'Nurse Daily Report '.$this->date->toDateString(),
            '',
            $link,
            'Download Spreadsheet'
        );

        if (isProductionEnv()) {
            $sara = User::whereEmail('sheller@circlelinkhealth.com')->first();

            if ($sara) {
                $sara->notify(new NurseDailyReportGenerated($this->date, $link));
            }
        }
    }
}
