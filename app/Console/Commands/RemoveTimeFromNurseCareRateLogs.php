<?php

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class RemoveTimeFromNurseCareRateLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nursecareratelogs:remove-time {fromId} {newDuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adjust time in Nurse Care Rate Logs';

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
     * @return void
     */
    public function handle()
    {
        $idStr       = $this->argument('fromId');
        $id          = intval($idStr);
        $durationStr = $this->argument('newDuration');
        $duration    = intval($durationStr);

        if ( ! $id || ! $duration) {
            $this->error("Invalid arguments: $idStr, $durationStr");

            return;
        }

        //find nurse care rate logs and adjust
        //nurse_care_rate_logs table

        /** @var NurseCareRateLog $careRateLog */
        $careRateLog = NurseCareRateLog::whereId($id)
                                       ->where('ccm_type', '=', 'accrued_after_ccm')
                                       ->first();

        if ( ! $careRateLog) {
            $this->error("Cannot modify activity. Please choose a different one. [no accrued_after_ccm]");

            return;
        }

        if ($careRateLog->increment < $duration) {
            $this->error("Cannot modify activity. Please lower duration to at least $careRateLog->increment. [duration > care rate log]");

            return;
        }

        $originalDuration = $careRateLog->increment;
        $decrementBy      = $originalDuration - $duration;

        $careRateLog->increment = $duration;

        $entriesToSave = collect();
        $entriesToSave->push($careRateLog);

        NurseCareRateLog::where('patient_user_id', '=', $careRateLog->patient_user_id)
                        ->where('created_at', '<=', $careRateLog->created_at->copy()->endOfMonth())
                        ->where('time_before', '>', $careRateLog->time_before)
                        ->orderBy('time_before', 'asc')
                        ->chunk(50, function ($items) use ($entriesToSave, $decrementBy) {
                            $items->each(function (NurseCareRateLog $item) use ($entriesToSave, $decrementBy) {
                                $item->time_before -= $decrementBy;
                                $entriesToSave->push($item);
                            });
                        });

        $entriesToSave->each(function (Model $item) {
            $item->save();
        });

        $count = $entriesToSave->count();
        $this->info("Done! Updated: $count records!");
    }
}
