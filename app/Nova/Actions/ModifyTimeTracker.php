<?php

namespace App\Nova\Actions;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Number;

class ModifyTimeTracker extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     *
     * @return void
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $durationStr = $fields->get('duration', null);
        $duration    = intval($durationStr);
        if ( ! $duration) {
            $this->markAsFailed($models->first(), "Need to supply a valid number for duration. Minimum 1.");

            return;
        }

        $msg = '';
        foreach ($models as $model) {
            /** @var PageTimer $timeRecord */
            $timeRecord = $model;

            if ($timeRecord->duration === $duration) {
                continue;
            }

            if ($duration > $timeRecord->duration) {
                $this->markAsFailed($timeRecord,
                    "Only decreasing duration is supported at the moment. Please supply a lower value than the existing or choose another record.");

                return;
            }

            $entriesToSave = collect();

            //lv_page_timer table
            $timeRecord->duration = $duration;
            $entriesToSave->push($timeRecord);

            if ($timeRecord->activity) {
                //lv_activities table
                $timeRecord->activity->duration = $duration;
                $entriesToSave->push($timeRecord->activity);

                /** @var NurseCareRateLog $careRateLog */
                $careRateLog = NurseCareRateLog::whereActivityId($timeRecord->activity->id)
                                               ->where('ccm_type', '=', 'accrued_after_ccm')
                                               ->first();

                if ( ! $careRateLog) {
                    $this->markAsFailed($timeRecord,
                        "Cannot modify activity. Please choose a different one. [no accrued_after_ccm]");

                    return;
                }

                if ($duration > $careRateLog->increment) {
                    $this->markAsFailed($timeRecord,
                        "Cannot modify activity. Please lower duration to at least $careRateLog->increment. [duration > care rate log]");

                    return;
                }

                //save pending entries
                $entriesToSave->each(function (Model $item) {
                    $item->save();
                });

                //adjust nurse care rate logs
                \Artisan::call('nursecareratelogs:remove-time', [
                    'fromId'      => $careRateLog->id,
                    'newDuration' => $duration,
                ]);

                //recalculate ccm/bhi time for patient (patient_monthly_summaries table)
                $startTime = Carbon::parse($timeRecord->start_time);
                \Artisan::call('ccm_time:recalculate', [
                    'dateString' => $startTime->toDateString(),
                    'userIds'    => $timeRecord->patient_id,
                ]);

                //re-generate invoice for nurse
                \Artisan::call('nurseinvoices:create', [
                    'month'   => $startTime->copy()->startOfMonth()->toDateString(),
                    'userIds' => $timeRecord->provider_id,
                ]);

            }
        }

        if ( ! empty($msg)) {
            $this->markAsFailed($models->first(),
                "Was not able to modify duration for entries: $msg");

            return;
        }

        $this->markAsFinished($models->first());
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Number::make('Duration (seconds)', 'duration'),
        ];
    }
}
