<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientMonthlyServiceTime;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use CircleLinkHealth\Timetracking\Services\TimeTrackerServerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ModifyPatientTime
{
    private bool $allowLessThan20Minutes;
    private string $chargeableServiceCode;
    private ?int $chargeableServiceId = null;
    private ?int $currentTimeSeconds  = 0;
    private ?Carbon $monthYear;
    private ?int $newTimeSeconds;
    private ?int $patientId;

    /**
     * ModifyPatientTime constructor.
     */
    public function __construct(int $patientId, string $chargeableServiceCode, int $newTimeSeconds, bool $allowLessThan20Minutes)
    {
        $this->patientId              = $patientId;
        $this->monthYear              = now()->startOfMonth();
        $this->chargeableServiceCode  = $chargeableServiceCode;
        $this->allowLessThan20Minutes = $allowLessThan20Minutes;
        $this->newTimeSeconds         = $newTimeSeconds;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $this->chargeableServiceId = ChargeableService::cached()
                                                      ->where('code', '=', $this->chargeableServiceCode)
                                                      ->first()
            ->id;

        $this->currentTimeSeconds = PatientMonthlyServiceTime::forChargeableServiceId($this->chargeableServiceId, $this->patientId, $this->monthYear);

        $this->validate();

        $activityIds         = $this->modifyActivities();
        $pageTimerIds        = $this->modifyPageTimers($activityIds);
        $nurseCareRateLogIds = $this->modifyNurseCareRateLogs($activityIds);

        $this->modifyPatientMonthlySummary();

        app(TimeTrackerServerService::class)->clearCache($this->patientId);

        $valid = $this->verifyChanges();
        if ( ! $valid) {
            $pageTimerIdsStr        = json_encode($pageTimerIds);
            $activityIdsStr         = json_encode($activityIds);
            $nurseCareRateLogIdsStr = json_encode($nurseCareRateLogIds);

            throw new \Exception("There was an issue modifying the time. Please review: PageTimer[$pageTimerIdsStr] | Activities[$activityIdsStr] | NurseCareRateLogs[$nurseCareRateLogIdsStr]");
        }

        //todo: unfulfill everything
        ProcessSinglePatientMonthlyServices::dispatch($this->patientId);

        $this->generateNurseInvoices($nurseCareRateLogIds);
    }

    private function generateNurseInvoices(array $nurseCareRateLogIds)
    {
        NurseCareRateLog::with('nurse')
                        ->whereIn('id', $nurseCareRateLogIds)
                        ->distinct('nurse_id')
                        ->select('nurse_id')
                        ->each(function (NurseCareRateLog $nurseLog) {
                            \Artisan::call('nurseinvoices:create', [
                                'month'   => now()->startOfMonth()->toDateString(),
                                'userIds' => $nurseLog->nurse->user_id,
                            ]);
                        });
    }

    private function getActivities(): Collection
    {
        return Activity::wherePatientId($this->patientId)
                       ->where('chargeable_service_id', '=', $this->chargeableServiceId)
                       ->whereBetween('performed_at', [
                           $this->monthYear->copy()->startOfMonth(),
                           $this->monthYear->copy()->endOfMonth(),
                       ])
                       ->orderBy('performed_at', 'desc')
                       ->get();
    }

    private function getNurseCareRateLogs(): Collection
    {
        return NurseCareRateLog::wherePatientUserId($this->patientId)
                               ->where('chargeable_service_id', '=', $this->chargeableServiceId)
                               ->whereBetween('performed_at', [
                                   $this->monthYear->copy()->startOfMonth(),
                                   $this->monthYear->copy()->endOfMonth(),
                               ])
                               ->orderBy('time_before', 'asc')
                               ->get();
    }

    private function getPageTimers(): Collection
    {
        return PageTimer::wherePatientId($this->patientId)
                        ->whereHas('logger', function ($q) {
                            $q->ofType(Role::CCM_TIME_ROLES);
                        })
                        ->where('chargeable_service_id', '=', $this->chargeableServiceId)
                        ->whereBetween('start_time', [
                            $this->monthYear->copy()->startOfMonth(),
                            $this->monthYear->copy()->endOfMonth(),
                        ])
                        ->orderBy('start_time', 'desc')
                        ->get();
    }

    private function modifyActivities(): array
    {
        $remaining = $this->currentTimeSeconds - $this->newTimeSeconds;
        $result    = collect();

        $this->getActivities()
             ->each(function (Activity $activity) use ($result, &$remaining) {
                 if ($remaining <= 0) {
                     return false;
                 }
                 if ($activity->duration >= $remaining) {
                     $activity->duration = $activity->duration - $remaining;
                     $remaining = 0;
                 } else {
                     $remaining -= $activity->duration;
                     $activity->duration = 0;
                 }
                 $activity->save();
                 $result->push($activity->id);
             });

        if ($remaining > 0) {
            Log::warning("Something's wrong modifying this patient's time.");
        }

        return $result->toArray();
    }

    private function modifyNurseCareRateLogs(array $activityIds): array
    {
        $result = collect();
        NurseCareRateLog::with([
                                   'activity' => fn ($q) => $q->select(['id', 'duration']),
                               ])
                        ->whereIn('activity_id', $activityIds)
                        ->orderByDesc('performed_at')
                        ->get()
                        ->groupBy('activity_id')
                        ->each(function (Collection $group) use ($result) {
                            $hasMoreThanOne = $group->count() > 1;
                            $remainingTime = $group->first()->activity->duration;
                            $group->each(function (NurseCareRateLog $nurseCareRateLog) use ($result, $hasMoreThanOne, &$remainingTime) {
                                if ($hasMoreThanOne) {
                                    if ('accrued_towards_ccm' === $nurseCareRateLog->ccm_type
                                        && ($nurseCareRateLog->time_before + $remainingTime) > CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS) {
                                        $nurseCareRateLog->increment = CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS - $nurseCareRateLog->time_before;
                                        $remainingTime = $remainingTime - $nurseCareRateLog->increment;
                                    } else {
                                        $nurseCareRateLog->increment = $remainingTime;
                                        $remainingTime = 0;
                                    }
                                } else {
                                    $nurseCareRateLog->increment = $nurseCareRateLog->activity->duration;
                                }
                                $nurseCareRateLog->save();
                                $result->push($nurseCareRateLog->id);
                            });
                        });

        $timeBefore = 0;
        $this->getNurseCareRateLogs()
             ->each(function (NurseCareRateLog $nurseCareRateLog) use (&$timeBefore) {
                 if ($nurseCareRateLog->time_before !== $timeBefore) {
                     $nurseCareRateLog->time_before = $timeBefore;
                     $nurseCareRateLog->save();
                 }
                 $timeBefore += $nurseCareRateLog->increment;
             });

        return $result->toArray();
    }

    private function modifyPageTimers(array $activityIds): array
    {
        $result = collect();

        PageTimer::with('activity')
                 ->whereHas('activity', function ($q) use ($activityIds) {
                     $q->whereIn('id', $activityIds);
                 })
                 ->each(function (PageTimer $pageTimer) use (&$remaining, $result) {
                     $pageTimer->duration = $pageTimer->activity->duration;
                     $pageTimer->save();
                     $result->push($pageTimer->id);
                 });

        return $result->toArray();
    }

    private function modifyPatientMonthlySummary()
    {
        /** @var PatientMonthlySummary $pms */
        $pms = PatientMonthlySummary::where('patient_id', '=', $this->patientId)
                                    ->where('month_year', '=', now()->startOfMonth()->toDateString())
                                    ->first();

        if (in_array($this->chargeableServiceCode, [ChargeableService::CCM, ChargeableService::GENERAL_CARE_MANAGEMENT, ChargeableService::PCM, ChargeableService::RPM])) {
            $pms->ccm_time = $this->newTimeSeconds;
        } else {
            $pms->bhi_time = $this->newTimeSeconds;
        }

        $pms->total_time = $pms->ccm_time + $pms->bhi_time;
        $pms->save();
    }

    private function validate()
    {
        if ($this->currentTimeSeconds < $this->newTimeSeconds) {
            $currentTimeMinutes = round($this->currentTimeSeconds / 60);

            throw new \Exception("You cannot add time to the patient. Current time is $currentTimeMinutes minutes.");
        }

        $minimum = CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
        if ( ! $this->allowLessThan20Minutes && $this->newTimeSeconds < $minimum) {
            $minMinutes = round($minimum / 60);

            throw new \Exception("You cannot reduce time to less than $minMinutes minutes.");
        }
    }

    private function verifyChanges(): bool
    {
        $pageTimerDuration         = $this->getPageTimers()->sum('duration');
        $activitiesDuration        = $this->getActivities()->sum('duration');
        $nurseCareRateLogsDuration = $this->getNurseCareRateLogs()->sum('increment');

        return $pageTimerDuration === $activitiesDuration && $pageTimerDuration === $nurseCareRateLogsDuration;
    }
}
