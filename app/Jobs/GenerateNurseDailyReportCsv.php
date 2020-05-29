<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Exports\NurseDailyReport;
use App\Notifications\ReportGenerated;
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

    const RECEIVES_DAILY_NURSE_REPORT_NOTIFICATION_NOVA_KEY = 'receives_nurse_daily_report_notification';
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
    public function handle()
    {
        $media = (new NurseDailyReport($this->date))->storeAndAttachMediaTo(SaasAccount::whereSlug('circlelink-health')->firstOrFail());

        $link = $media->getUrl();

        if (isProductionEnv()) {
            $receivers = User::whereIn('id', function ($q) {
                $q->select('config_value')
                    ->from('app_config')
                    ->where('config_key', self::RECEIVES_DAILY_NURSE_REPORT_NOTIFICATION_NOVA_KEY);
            })->get()->each(function ($user) use ($link) {
                $user->notify(new ReportGenerated($this->date, $link, 'Nurse Daily Report'));
            });
        }
    }
}
