<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Jobs\ProcessMonthltyPatientTime;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ReArrangeActivityChargeableServices extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command makes sure that all activities are arranged correctly to their chargeable services';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rearrange:activities-and-chargeable-services {month}';

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
        $this->process(ChargeableService::CCM, ChargeableService::RPM);
        $this->process(ChargeableService::CCM_PLUS_40);

        return 0;
    }

    private function changeChargeableServiceOfActivity(Activity $activity): bool
    {
        /** @var ?string $newCsCode */
        $newCsCode = $this->getNextCsCode($this->getCsCode($activity));
        if ( ! $newCsCode) {
            return false;
        }
        $activity->chargeable_service_id = $this->getCsId($newCsCode);
        $activity->save();

        sendSlackMessage('#time-tracking-issues', "ReArrangeActivityChargeableServices: Activity[$activity->id] has been modified. Please review.");

        NurseCareRateLog::where('activity_id', '=', $activity->id)
            ->update([
                'chargeable_service_id' => $activity->chargeable_service_id,
            ]);

        $this->dispatchPostProcessing($activity);

        return true;
    }

    private function dispatchPostProcessing(Activity $activity)
    {
        ProcessMonthltyPatientTime::dispatchNow($activity->patient_id);
        event(new PatientActivityCreated($activity->patient_id, false));
    }

    private function getCsCode(Activity $activity): string
    {
        return ChargeableService::cached()
            ->firstWhere('id', '=', $activity->chargeable_service_id)
            ->code;
    }

    private function getCsId(?string $csCode): ?int
    {
        if ( ! $csCode) {
            return null;
        }

        return ChargeableService::cached()
            ->firstWhere('code', '=', $csCode)
            ->id;
    }

    private function getNextCsCode(?string $csCode): ?string
    {
        if ( ! $csCode) {
            return null;
        }
        switch ($csCode) {
            case ChargeableService::CCM:
                return ChargeableService::CCM_PLUS_40;
            case ChargeableService::CCM_PLUS_40:
                return ChargeableService::CCM_PLUS_60;
            case ChargeableService::RPM:
                return ChargeableService::RPM40;
            default:
                return null;
        }
    }

    private function modifyNurseCareRateLogsForNewActivity(Activity $currentActivity, Activity $nextActivity)
    {
        NurseCareRateLog::where('activity_id', '=', $currentActivity->id)
            ->orderBy('time_before', 'asc')
            ->get()
            ->each(function (NurseCareRateLog $nurseCareRateLog, int $key) use ($nextActivity) {
                //key 0 will keep $activity->id
                if (0 === $key) {
                    return;
                }
                $nurseCareRateLog->activity_id = $nextActivity->id;
                $nurseCareRateLog->chargeable_service_id = $nextActivity->chargeable_service_id;
                $nurseCareRateLog->save();
            });
    }

    private function month(): Carbon
    {
        return Carbon::parse($this->argument('month'))->startOfMonth();
    }

    private function moveDurationToNewActivity(Activity $activity, int $timeToRemove): bool
    {
        $nextCsCode = $this->getNextCsCode($this->getCsCode($activity));
        if ( ! $nextCsCode) {
            return false;
        }

        $activity->duration -= $timeToRemove;
        $activity->save();

        $pageTimer                = new PageTimer();
        $pageTimer->activity_type = $activity->type;
        $pageTimer->patient_id    = $activity->patient_id;
        $pageTimer->provider_id   = $activity->provider_id;
        $pageTimer->start_time    = $activity->performed_at;

        $nextCsId                  = $this->getCsId($nextCsCode);
        $chargeableServiceDuration = new ChargeableServiceDuration($nextCsId, $timeToRemove, ChargeableService::BHI === $nextCsCode);
        $nextActivity              = app(PatientServiceProcessorRepository::class)->createActivityForChargeableService('rearrange-activity', $pageTimer, $chargeableServiceDuration);

        sendSlackMessage('#time-tracking-issues', "ReArrangeActivityChargeableServices: Activities $activity->id and $nextActivity->id have been modified. Please review.");

        $this->modifyNurseCareRateLogsForNewActivity($activity, $nextActivity);
        $this->dispatchPostProcessing($nextActivity);

        return true;
    }

    private function process(...$codes)
    {
        $ids = ChargeableService::cached()
            ->whereIn('code', $codes)
            ->pluck('id')
            ->all();

        ChargeablePatientMonthlyTime::where('chargeable_month', '=', $this->month())
            ->whereIn('chargeable_service_id', $ids)
            ->where('total_time', '>', CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS)
            ->orderBy('chargeable_service_id')
            ->chunk(100, function (Collection $summaries) {
                $summaries->each(function (ChargeablePatientMonthlyTime $summary) {
                    $timeToRemove = $summary->total_time - CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
                    $activitiesToModify = collect();
                    Activity::where('chargeable_service_id', '=', $summary->chargeable_service_id)
                        ->where('patient_id', '=', $summary->patient_user_id)
                        ->whereBetween('performed_at', [
                            Carbon::parse($summary->chargeable_month)->startOfMonth(),
                            Carbon::parse($summary->chargeable_month)->endOfMonth(),
                        ])
                        ->orderBy('performed_at', 'desc')
                        ->limit(10)
                        ->each(function (Activity $activity) use ($timeToRemove, $activitiesToModify) {
                            $activitiesToModify->push($activity);
                            if ($activitiesToModify->sum('duration') >= $timeToRemove) {
                                return false;
                            }
                        });

                    $remainingToRemove = $timeToRemove;
                    $activitiesToModify->each(function (Activity $activity) use (&$remainingToRemove) {
                        if ($remainingToRemove <= 0) {
                            return false;
                        }
                        if ($activity->duration <= $remainingToRemove) {
                            $this->changeChargeableServiceOfActivity($activity);
                            $remainingToRemove -= $activity->duration;
                        } else {
                            $this->moveDurationToNewActivity($activity, $remainingToRemove);
                            $remainingToRemove = 0;
                        }
                    });
                });
            });
    }
}
