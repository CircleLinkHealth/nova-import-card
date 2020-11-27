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
use Illuminate\Support\Facades\Log;
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

    private bool $allowLessThan20Minutes = false;
    private ?int $chargeableServiceId    = null;
    private ?int $currentTimeSeconds     = 0;
    private ?Carbon $monthYear           = null;
    private ?int $newTimeSeconds         = 0;
    private ?int $patientId              = null;

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Chargeable Service', 'chargeable_service')
                ->required(true)
                ->options([
                    ChargeableService::CCM                     => 'CCM',
                    ChargeableService::GENERAL_CARE_MANAGEMENT => 'CCM (RHC/FQHC)',
                    ChargeableService::BHI                     => 'BHI',
                    ChargeableService::PCM                     => 'PCM',
                    ChargeableService::RPM                     => 'RPM',
                ]),

            Number::make('Enter new duration (minutes)', 'durationMinutes')
                ->required(true),

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
        $this->monthYear              = now()->startOfMonth();
        $this->allowLessThan20Minutes = $fields->get('allow_accrued_towards', false);
        $this->newTimeSeconds         = $fields->get('durationMinutes') * 60;

        /** @var string $chargeableServiceCode */
        $chargeableServiceCode     = $fields->get('chargeable_service');
        $this->chargeableServiceId = ChargeableService::cached()
            ->where('code', '=', $chargeableServiceCode)
            ->first()
            ->id;

        /** @var User $patient */
        $patient                  = $models->first();
        $this->patientId          = $patient->id;
        $this->currentTimeSeconds = PatientMonthlyServiceTime::forChargeableServiceId($this->chargeableServiceId, $this->patientId, $this->monthYear);
        $pageTimers               = $this->getPageTimers();

        $errorMsg = $this->validate($models);
        if ($errorMsg) {
            $this->markAsFailed($models->first(), $errorMsg);

            return;
        }

        $pageTimerIds        = $this->modifyPageTimers($pageTimers);
        $activityIds         = $this->modifyActivities($pageTimerIds);
        $nurseCareRateLogIds = $this->modifyNurseCareRateLogs($activityIds);

        $this->modifyPatientMonthlySummary($chargeableServiceCode);

        $valid = $this->verifyChanges();
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

    private function getActivities(): Collection
    {
        return $this->getActivitiesQuery()->get();
    }

    private function getActivitiesQuery()
    {
        return Activity::wherePatientId($this->patientId)
            ->where('chargeable_service_id', '=', $this->chargeableServiceId)
            ->whereBetween('performed_at', [
                $this->monthYear->copy()->startOfMonth(),
                $this->monthYear->copy()->endOfMonth(),
            ])
            ->orderBy('performed_at', 'desc');
    }

    private function getManualActivities()
    {
        return $this->getActivitiesQuery()
            ->where('logged_from', '=', 'manual_input')
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
            ->where('chargeable_service_id', '=', $this->chargeableServiceId)
            ->whereBetween('start_time', [
                $this->monthYear->copy()->startOfMonth(),
                $this->monthYear->copy()->endOfMonth(),
            ])
            ->orderBy('start_time', 'desc')
            ->get();
    }

    private function modifyActivities(array $pageTimerIds): array
    {
        $remaining = $this->currentTimeSeconds - $this->newTimeSeconds;
        $result    = collect();
        Activity::with([
            'pageTime' => fn ($q) => $q->select(['id', 'duration']),
        ])
            ->whereIn('page_timer_id', $pageTimerIds)
            ->orderByDesc('performed_at')
            ->each(function (Activity $activity) use ($result, &$remaining) {
                $remaining = $remaining - ($activity->pageTime->duration - $activity->duration);
                $result->push($activity->id);
                $activity->duration = $activity->pageTime->duration;
                $activity->save();
            });

        if ($remaining > 0) {
            $this->getManualActivities()
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
        }

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

    private function modifyPageTimers(Collection $pageTimers): array
    {
        $result    = collect();
        $remaining = $this->currentTimeSeconds - $this->newTimeSeconds;
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

    private function modifyPatientMonthlySummary(string $chargeableServiceCode)
    {
        /** @var PatientMonthlySummary $pms */
        $pms = PatientMonthlySummary::where('patient_id', '=', $this->patientId)
            ->where('month_year', '=', now()->startOfMonth()->toDateString())
            ->first();

        if (in_array($chargeableServiceCode, [ChargeableService::CCM, ChargeableService::GENERAL_CARE_MANAGEMENT, ChargeableService::PCM, ChargeableService::RPM])) {
            $pms->ccm_time = $this->newTimeSeconds;
        } else {
            $pms->bhi_time = $this->newTimeSeconds;
        }

        $pms->total_time = $pms->ccm_time + $pms->bhi_time;
        $pms->save();
    }

    private function validate(Collection $models): ?string
    {
        if ($models->count() > 1) {
            return 'Please run this action for a single patient at a time.';
        }

        if ($this->currentTimeSeconds < $this->newTimeSeconds) {
            $currentTimeMinutes = round($this->currentTimeSeconds / 60);

            return "You cannot add time to the patient. Current time is $currentTimeMinutes minutes.";
        }

        $minimum = Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
        if ( ! $this->allowLessThan20Minutes && $this->newTimeSeconds < $minimum) {
            $minMinutes = round($minimum / 60);

            return "You cannot reduce time to less than $minMinutes minutes.";
        }

        return null;
    }

    private function verifyChanges(): bool
    {
        $pageTimerDuration         = $this->getPageTimers()->sum('duration');
        $activitiesDuration        = $this->getActivities()->sum('duration');
        $nurseCareRateLogsDuration = $this->getNurseCareRateLogs()->sum('increment');

        return $pageTimerDuration === $activitiesDuration && $pageTimerDuration === $nurseCareRateLogsDuration;
    }
}
