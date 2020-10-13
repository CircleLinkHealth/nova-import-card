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
    private function getCareRateLogs(int $pageTimerId, Collection $activityIds, int $newDuration, bool $allowAccruedTowards)
    {
        /** @var Collection|NurseCareRateLog[] $careRateLogs */
        $careRateLogs = NurseCareRateLog::whereIn('activity_id', $activityIds)
            ->orderBy('id', 'asc')
            ->get();
        if ($careRateLogs->isEmpty()) {
            throw new Exception("Something's wrong. Should not reach here.");
        }

        if ( ! $allowAccruedTowards && 1 === $careRateLogs->count() && 'accrued_towards_ccm' === $careRateLogs->first()->ccm_type) {
            $msg = "Cannot modify time tracker entry[$pageTimerId]. Could not find nurse care rate logs. Please choose a different one.";
            if ( ! $allowAccruedTowards) {
                $msg .= ' [no accrued_after_ccm]';
            }
            throw new Exception($msg);
        }

        $careRateLogsToModify = [];
        $zeroOut              = false;
        foreach ($careRateLogs as $careRateLog) {
            if ($zeroOut) {
                $careRateLogsToModify[$careRateLog->id] = 0;
            } elseif ($careRateLog->increment >= $newDuration) {
                $careRateLogsToModify[$careRateLog->id] = $newDuration;
                $zeroOut                                = true;
            } else {
                $newDuration -= $careRateLog->increment;
            }
        }

        return $careRateLogsToModify;
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
            $this->processActivities($activities, $duration, $entriesToSave);
            $careRateLogsToModify = $this->getCareRateLogs($timeRecord->id, $activities->pluck('id'), $duration, $allowAccruedTowards);
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

    private function processActivities(Collection $activities, int $newDuration, Collection $entriesToSave)
    {
        $zeroOut = false;
        foreach ($activities as $activity) {
            if ($zeroOut) {
                $activity->duration = 0;
                $entriesToSave->push($activity);
            } elseif ($activity->duration >= $newDuration) {
                $activity->duration = $newDuration;
                $zeroOut            = true;
                $entriesToSave->push($activity);
            } else {
                $newDuration -= $activity->duration;
            }
        }
    }
}
