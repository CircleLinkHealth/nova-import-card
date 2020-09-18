<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
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
    private function modifyRecords(PageTimer $timeRecord, int $duration, bool $allowAccruedTowards = false)
    {
        $entriesToSave = collect();

        //lv_page_timer table
        $timeRecord->duration = $duration;
        $entriesToSave->push($timeRecord);

        $hasBillableActivity = false;
        if ($timeRecord->activity) {
            $hasBillableActivity = true;
            //lv_activities table
            $timeRecord->activity->duration = $duration;
            $entriesToSave->push($timeRecord->activity);

            /** @var NurseCareRateLog $careRateLog */
            $careRateLogQuery = NurseCareRateLog::whereActivityId($timeRecord->activity->id);
            if ( ! $allowAccruedTowards) {
                $careRateLogQuery->where('ccm_type', '=', 'accrued_after_ccm');
            }

            $careRateLog = $careRateLogQuery->first();

            //some more validation here, simply because the current implementation supports simple use cases
            if ( ! $careRateLog) {
                $msg = 'Cannot modify activity. Please choose a different one.';
                if ( ! $allowAccruedTowards) {
                    $msg .= ' [no accrued_after_ccm]';
                }
                throw new Exception($msg);
            }

            if ($duration > $careRateLog->increment) {
                throw new Exception("Cannot modify activity. Please lower duration to at least $careRateLog->increment. [duration > care rate log]");
            }
        }

        //now, that all validation passes, save pending entries
        $entriesToSave->each(function (Model $item) {
            $item->save();
        });

        $startTime = Carbon::parse($timeRecord->start_time);
        if ($hasBillableActivity) {
            //adjust nurse care rate logs
            \Artisan::call('nursecareratelogs:remove-time', [
                'fromId'              => $careRateLog->id,
                'newDuration'         => $duration,
                'allowAccruedTowards' => $allowAccruedTowards,
            ]);

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
