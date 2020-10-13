<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;

class ModifyTimeTracker extends Action implements ShouldQueue
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
            Number::make('Enter new duration (seconds)', 'duration'),
            Boolean::make('Force', 'allow_accrued_towards'),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * Due to time tracking bugs, sometimes we have to manually reduce duration of an activity.
     * Note: one might have to update entries in:
     *  lv_page_timer - anything that relates to time tracked goes here
     *  lv_activities - anything that relates to billable (towards patient/practice) time tracked
     *  nurse_care_rate_logs - anything that relates to billable (towards care coach) time tracked
     *  patient_monthly_summaries - match new ccm_time or bhi_time
     *
     * @return void
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $durationStr = $fields->get('duration', null);
        $duration    = intval($durationStr);
        if ( ! $duration || $duration < 1) {
            $this->markAsFailed($models->first(), 'Need to supply a valid number for duration. Minimum 1.');

            return;
        }

        foreach ($models as $model) {
            /** @var PageTimer $timeRecord */
            $timeRecord = $model;

            if ($timeRecord->duration === $duration) {
                continue;
            }

            if ($duration > $timeRecord->duration) {
                $this->markAsFailed(
                    $timeRecord,
                    'Only decreasing duration is supported at the moment. Please supply a lower value than the existing or choose another record.'
                );

                return;
            }

            try {
                $this->modifyRecords($timeRecord, $duration, $fields->get('allow_accrued_towards', false));
            } catch (Exception $e) {
                $this->markAsFailed($timeRecord, $e->getMessage());

                return;
            }
        }

        $this->markAsFinished($models->first());
    }

    /**
     * @throws Exception
     */
    private function getBillableActivity(PageTimer $pageTimer, int $duration): ?Activity
    {
        $hasBillable = $pageTimer->activities->isNotEmpty();
        foreach ($pageTimer->activities as $activity) {
            if ($activity->duration >= $duration) {
                return $activity;
            }
        }

        if ($hasBillable) {
            $msg = "Cannot modify time tracker entry[$pageTimer->id]. Could not find activity with higher duration than $duration.";
            throw new Exception($msg);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function getCareRateLog(int $activityId, int $duration, bool $allowAccruedTowards = false): NurseCareRateLog
    {
        $careRateLogQuery = NurseCareRateLog::whereActivityId($activityId);
        if ( ! $allowAccruedTowards) {
            $careRateLogQuery->where('ccm_type', '=', 'accrued_after_ccm');
        }

        $careRateLogs = $careRateLogQuery->get();
        if ($careRateLogs->isEmpty()) {
            //some more validation here, simply because the current implementation supports simple use cases
            $msg = "Cannot modify activity[$activityId]. Could not find nurse care rate logs. Please choose a different one.";
            if ( ! $allowAccruedTowards) {
                $msg .= ' [no accrued_after_ccm]';
            }
            throw new Exception($msg);
        }

        /** @var NurseCareRateLog $careRateLog */
        $careRateLog = null;
        $careRateLogs->each(function (NurseCareRateLog $entry) use ($duration, &$careRateLog) {
            if ($entry->increment >= $duration) {
                $careRateLog = $entry;
            }
        });

        if ( ! $careRateLog) {
            /** @var NurseCareRateLog $firstCareRateLog */
            $firstCareRateLog = $careRateLogs->first();
            throw new Exception("Cannot modify activity[$activityId]. Please lower duration to at least {$firstCareRateLog->increment}[{$firstCareRateLog->id}]. [duration > care rate log]");
        }

        return $careRateLog;
    }

    /**
     * @throws Exception
     */
    private function modifyRecords(PageTimer $timeRecord, int $duration, bool $allowAccruedTowards = false)
    {
        $entriesToSave = collect();

        $timeRecord->duration = $duration;
        $entriesToSave->push($timeRecord);

        /** @var NurseCareRateLog[] $careRateLogsToModify */
        $careRateLogsToModify = [];
        /** @var Activity[]|Collection $activities */
        $activities = $timeRecord->activities()->orderBy('id', 'asc')->get();
        if ($activities->isNotEmpty()) {
            $zeroOut      = false;
            $durationCopy = $duration;
            foreach ($activities as $activity) {
                if ($zeroOut) {
                    $activity->duration = 0;
                    $entriesToSave->push($activity);
                } elseif ($activity->duration >= $durationCopy) {
                    $activity->duration = $durationCopy;
                    $zeroOut            = true;
                    $entriesToSave->push($activity);
                } else {
                    $durationCopy -= $activity->duration;
                }
            }

            /** @var Collection|NurseCareRateLog[] $careRateLogs */
            $careRateLogs = NurseCareRateLog::whereIn('activity_id', $activities->pluck('id'))
                ->orderBy('id', 'asc')
                ->get();
            if ($careRateLogs->isEmpty()) {
                throw new Exception("Something's wrong. Should not reach here.");
            }

            if ( ! $allowAccruedTowards && 1 === $careRateLogs->count() && 'accrued_towards_ccm' === $careRateLogs->first()->ccm_type) {
                $msg = "Cannot modify time tracker entry[$timeRecord->id]. Could not find nurse care rate logs. Please choose a different one.";
                if ( ! $allowAccruedTowards) {
                    $msg .= ' [no accrued_after_ccm]';
                }
                throw new Exception($msg);
            }

            $zeroOut      = false;
            $durationCopy = $duration;
            foreach ($careRateLogs as $careRateLog) {
                if ($zeroOut) {
                    $careRateLogsToModify[$careRateLog->id] = 0;
                } elseif ($careRateLog->increment >= $durationCopy) {
                    $careRateLogsToModify[$careRateLog->id] = $durationCopy;
                    $zeroOut                                = true;
                } else {
                    $durationCopy -= $careRateLog->increment;
                }
            }
        }

        $entriesToSave->each(function (Model $item) {
            $item->save();
        });

        $startTime = Carbon::parse($timeRecord->start_time);
        if ($activities->isNotEmpty()) {
            foreach ($careRateLogsToModify as $careRateLogId => $careRateLogDuration) {
                \Artisan::call('nursecareratelogs:remove-time', [
                    'fromId'              => $careRateLogId,
                    'newDuration'         => $careRateLogDuration,
                    'allowAccruedTowards' => $allowAccruedTowards,
                ]);
            }

            //if this was a billable activity, we have to
            //recalculate ccm/bhi time for patient (patient_monthly_summaries table)
            \Artisan::call('ccm_time:recalculate', [
                'dateString' => $startTime->toDateString(),
                'userIds'    => $timeRecord->patient_id,
            ]);
        }

        //always re-generate invoice for nurse
        \Artisan::call('nurseinvoices:create', [
            'month'   => $startTime->copy()->startOfMonth()->toDateString(),
            'userIds' => $timeRecord->provider_id,
        ]);
    }
}
