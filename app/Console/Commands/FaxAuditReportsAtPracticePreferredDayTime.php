<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\Channels\FaxChannel;
use App\Notifications\SendAuditReport;
use App\Reports\PatientDailyAuditReport;
use App\Services\Phaxio\PhaxioFaxService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CustomerNotificationContactTimePreference;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class FaxAuditReportsAtPracticePreferredDayTime extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends audit reports via eFax to practices that have defined custom days times thhey want to receive faxes.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'efax-custom-algo:audit-reports {reportsOfMonth?} {--dry}';
    /**
     * @var Carbon
     */
    private $date;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CustomerNotificationContactTimePreference::where('day', now()->format('l'))
            ->where('from', '>=', now()->startOfHour()->format('H:i'))
            ->where('to', '<=', now()->endOfHour()->format('H:i'))
            ->where('notification', SendAuditReport::class)
            ->where('is_enabled', true)
            ->chunkById(100, function ($preferences) {
                foreach ($preferences as $preference) {
                    $key = $preference->cacheKey(CustomerNotificationContactTimePreference::AUDIT_REPORTS_FAXES_PER_HOUR);

                    if (is_numeric($preference->max_per_hour) && $this->hourlyLimitReached($key, $preference->max_per_hour)) {
                        continue;
                    }

                    $this->sendNotification($preference->contactable_type, $preference->contactable_id, $key, $this->forMonth());
                }
            });
    }

    private function forMonth()
    {
        if (is_null($this->date)) {
            if ($inputDate = $this->argument('reportsOfMonth')) {
                $this->date = Carbon::createFromFormat('Y-m-d', $inputDate)->firstOfMonth();
            } else {
                $this->date = now()->subMonth()->firstOfMonth();
            }
        }

        return $this->date;
    }

    private function hourlyLimitReached(string $key, int $max): bool
    {
        return $this->notificationsSentThisHour($key) >= $max;
    }

    private function notificationsSentThisHour(string $key): int
    {
        return \Cache::get($key);
    }

    private function sendNotification(string $contactable_type, int $contactable_id, string $key, Carbon $date)
    {
        $user = User::ofType('participant')
            ->with([
                'patientInfo',
                'patientSummaries',
                'primaryPractice',
                'primaryPractice.settings',
            ])
            ->whereHas('primaryPractice.notificationContactPreferences', function ($q) {
                return             $q->where('notification', SendAuditReport::class);
            })
            ->whereHas('primaryPractice', function ($query) {
                $query->where('active', '=', true)
                    ->whereHas('settings', function ($query) {
                        $query->where('efax_audit_reports', '=', true);
                    })->when($this->argument('practiceId'), function ($q) {
                        $q->where('id', '=', $this->argument('practiceId'));
                    });
            })
            ->whereHas('patientSummaries', function ($query) use ($date) {
                $query->where('total_time', '>', 0)
                    ->where('month_year', $date);
            })
            ->whereDoesntHave('patientInfo.notificationsAboutThisPatient', function ($query) use ($date) {
                $monthNotificationSent = $date->copy()->addMonth();

                $query->where('type', SendAuditReport::class)
                    ->whereBetween('created_at', [
                        $monthNotificationSent->startOfMonth(),
                        $monthNotificationSent->endOfMonth(),
                    ])
                    ->where('media_collection_name', PatientDailyAuditReport::mediaCollectionName($date))
                    ->where('phaxio_event_status', PhaxioFaxService::EVENT_STATUS_SUCCESS)
                    ->where('phaxio_event_type', PhaxioFaxService::EVENT_TYPE_FAX_COMPLETED);
            })->first();

        if ( ! $user) {
            return;
        }

        $shouldBatch = (bool) $user->primaryPractice->cpmSettings()->batch_efax_audit_reports;

        $user->locations->each(function (Location $location) use ($user, $date, $shouldBatch) {
            $location->notify(new SendAuditReport($user, $date, [FaxChannel::class], $shouldBatch));
        });
    }
}
