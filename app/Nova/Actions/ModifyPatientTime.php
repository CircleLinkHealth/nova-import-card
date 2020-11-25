<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Constants;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientMonthlyServiceTime;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;

class ModifyPatientTime extends Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Chargeable Service', 'chargeable_service')->options([
                ChargeableService::CCM                     => 'CCM',
                ChargeableService::GENERAL_CARE_MANAGEMENT => 'CCM (RHC/FQHC)',
                ChargeableService::BHI                     => 'BHI',
                ChargeableService::PCM                     => 'PCM',
                ChargeableService::RPM                     => 'RPM',
            ]),
            Number::make('Enter new duration (seconds)', 'duration'),
            Boolean::make('Force (even if less than 20 minutes)', 'allow_accrued_towards'),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $monthYear = now()->startOfMonth();

        $allowLessThan20Minutes = $fields->get('allow_accrued_towards', false);

        /** @var string $chargeableServiceCode */
        $chargeableServiceCode = $fields->get('chargeable_service');

        /** @var int $newDuration */
        $newDuration = $fields->get('duration');

        /** @var int $chargeableServiceId */
        $chargeableServiceId = ChargeableService::cached()
            ->where('code', '=', $chargeableServiceCode)
            ->first()
            ->id;

        /** @var User $patient */
        $patient     = $models->first();
        $pageTimers  = $this->getPageTimers($patient->id, $chargeableServiceId, $monthYear);
        $currentTime = PatientMonthlyServiceTime::forChargeableServiceId($chargeableServiceId, $patient->id, $monthYear);

        $errorMsg = $this->validate($models, $chargeableServiceCode, $currentTime, $newDuration, $allowLessThan20Minutes, $pageTimers);
        if ($errorMsg) {
            $this->markAsFailed($models->first(), $errorMsg);

            return;
        }

        $pageTimerIds        = $this->modifyPageTimers($pageTimers, $currentTime, $newDuration);
        $activityIds         = $this->modifyActivities($pageTimerIds);
        $nurseCareRateLogIds = $this->modifyNurseCareRateLogs($activityIds);

        if (in_array($chargeableServiceCode, [ChargeableService::CCM, ChargeableService::BHI])) {
            $this->modifyPatientMonthlySummary($patient->id, $chargeableServiceCode, $newDuration);
        }

        $valid = $this->verifyChanges($patient->id, $chargeableServiceId, $monthYear);
        if ( ! $valid) {
            $pageTimerIdsStr        = json_encode($pageTimerIds);
            $activityIdsStr         = json_encode($activityIds);
            $nurseCareRateLogIdsStr = json_encode($nurseCareRateLogIds);
            $this->markAsFailed($models->first(), "There was an issue modifying the time. Please review: PageTimer[$pageTimerIdsStr] | Activities[$activityIdsStr] | NurseCareRateLogs[$nurseCareRateLogIdsStr]");
        }

        //todo: unfulfill everything
        ProcessSinglePatientMonthlyServices::dispatch($patient->id);

        \Artisan::call('nurseinvoices:create', [
            'month'   => now()->startOfMonth()->toDateString(),
            'userIds' => $patient->id,
        ]);

        $this->markAsFinished($models->first());
    }

    private function getActivities(int $patientId, int $chargeableServiceId, Carbon $monthYear): Collection
    {
        return Activity::wherePatientId($patientId)
            ->where('chargeable_service_id', '=', $chargeableServiceId)
            ->whereBetween('performed_at', [
                $monthYear->copy()->startOfMonth(),
                $monthYear->copy()->endOfMonth(),
            ])
            ->orderBy('performed_at', 'desc')
            ->get();
    }

    private function getNurseCareRateLogs(int $patientId, int $chargeableServiceId, Carbon $monthYear): Collection
    {
        return NurseCareRateLog::wherePatientUserId($patientId)
            ->where('chargeable_service_id', '=', $chargeableServiceId)
            ->whereBetween('performed_at', [
                $monthYear->copy()->startOfMonth(),
                $monthYear->copy()->endOfMonth(),
            ])
            ->orderBy('time_before', 'asc')
            ->get();
    }

    private function getPageTimers(int $patientId, int $chargeableServiceId, Carbon $monthYear): Collection
    {
        return PageTimer::wherePatientId($patientId)
            ->where('chargeable_service_id', '=', $chargeableServiceId)
            ->whereBetween('start_time', [
                $monthYear->copy()->startOfMonth(),
                $monthYear->copy()->endOfMonth(),
            ])
            ->orderBy('start_time', 'desc')
            ->get();
    }

    private function modifyActivities(array $pageTimerIds): array
    {
        $result = collect();
        Activity::with([
            'pageTime' => fn ($q) => $q->select(['id', 'duration']),
        ])
            ->whereIn('page_timer_id', $pageTimerIds)
            ->orderByDesc('performed_at')
            ->each(function (Activity $activity) use ($result) {
                $result->push($activity->id);
                $activity->duration = $activity->pageTime->duration;
                $activity->save();
            });

        return $result->toArray();
    }

    private function modifyNurseCareRateLogs(array $activityIds): array
    {
        $patientId           = null;
        $monthYear           = null;
        $chargeableServiceId = null;
        $result              = collect();
        NurseCareRateLog::with([
            'activity' => fn ($q) => $q->select(['id', 'duration']),
        ])
            ->whereIn('activity_id', $activityIds)
            ->orderByDesc('performed_at')
            ->get()
            ->groupBy('activity_id')
            ->each(function (Collection $group) use ($result, &$patientId, &$monthYear, &$chargeableServiceId) {
                $hasMoreThanOne = $group->count() > 1;
                $remainingTime = $group->first()->activity->duration;
                $group->each(function (NurseCareRateLog $nurseCareRateLog) use ($result, $hasMoreThanOne, &$remainingTime, &$patientId, &$monthYear, &$chargeableServiceId) {
                    if ( ! $patientId) {
                        $patientId = $nurseCareRateLog->patient_user_id;
                        $monthYear = $nurseCareRateLog->performed_at->startOfMonth();
                        $chargeableServiceId = $nurseCareRateLog->chargeable_service_id;
                    }

                    if ($hasMoreThanOne) {
                        if ('accrued_towards_ccm' === $nurseCareRateLog->ccm_type
                            && ($nurseCareRateLog->time_before + $remainingTime) > Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS) {
                            $nurseCareRateLog->increment = Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS - $nurseCareRateLog->time_before;
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

        if ($patientId) {
            $timeBefore = 0;
            $this->getNurseCareRateLogs($patientId, $chargeableServiceId, $monthYear)
                ->each(function (NurseCareRateLog $nurseCareRateLog) use (&$timeBefore) {
                    if ($nurseCareRateLog->time_before !== $timeBefore) {
                        $nurseCareRateLog->time_before = $timeBefore;
                        $nurseCareRateLog->save();
                    }
                    $timeBefore += $nurseCareRateLog->increment;
                });
        }

        return $result->toArray();
    }

    private function modifyPageTimers(Collection $pageTimers, int $currentDuration, int $newDuration): array
    {
        $result    = collect();
        $remaining = $currentDuration - $newDuration;
        $pageTimers->each(function (PageTimer $pageTimer) use (&$remaining, $result) {
            if ($remaining <= 0) {
                return false;
            }
            if ($pageTimer->duration >= $remaining) {
                $pageTimer->duration = $pageTimer->duration - $remaining;
                $remaining = 0;
            } else {
                $remaining -= $pageTimer->duration;
                $pageTimer->duration = 0;
            }
            $pageTimer->save();
            $result->push($pageTimer->id);
        });

        return $result->toArray();
    }

    private function modifyPatientMonthlySummary(int $patientId, string $chargeableServiceCode, int $newDuration)
    {
        /** @var PatientMonthlySummary $pms */
        $pms = PatientMonthlySummary::where('patient_id', '=', $patientId)
            ->where('month_year', '=', now()->startOfMonth()->toDateString())
            ->first();

        if (ChargeableService::CCM === $chargeableServiceCode) {
            $pms->ccm_time = $newDuration;
        } else {
            $pms->bhi_time = $newDuration;
        }
        $pms->save();
    }

    private function validate(Collection $models, string $chargeableService, int $currentTime, int $newTime, bool $allowLessThan20Minutes, Collection $pageTimers): ?string
    {
        if ($models->count() > 1) {
            return 'Please run this action for a single patient at a time.';
        }

        if ($currentTime < $newTime) {
            return "You cannot add time to the patient. Current time is $currentTime seconds.";
        }

        $minimum = Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
        if ( ! $allowLessThan20Minutes && $newTime < $minimum) {
            return "You cannot reduce time to less than $minimum seconds.";
        }

        $currentDuration = $pageTimers->sum('duration');
        if ($currentDuration < $newTime) {
            $max = $currentDuration - 1;

            return "You cannot add time to patient. Max new duration: $max.";
        }

        return null;
    }

    private function verifyChanges(int $patientId, int $chargeableServiceId, Carbon $monthYear): bool
    {
        $pageTimerDuration         = $this->getPageTimers($patientId, $chargeableServiceId, $monthYear)->sum('duration');
        $activitiesDuration        = $this->getActivities($patientId, $chargeableServiceId, $monthYear)->sum('duration');
        $nurseCareRateLogsDuration = $this->getNurseCareRateLogs($patientId, $chargeableServiceId, $monthYear)->sum('increment');

        return $pageTimerDuration === $activitiesDuration && $pageTimerDuration === $nurseCareRateLogsDuration;
    }
}
