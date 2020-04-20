<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Call;
use App\Note;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNurseCareRateLogs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set patient_user_id and is_successful_call in nurse_care_rate_logs in case there are inconsistencies';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nursecareratelogs:fix {fromMonth}';

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
        $month = Carbon::parse($this->argument('fromMonth'))->startOfMonth();

        $count = 0;
        $chunk = 50;

        DB::table('nurse_care_rate_logs')
            ->where('created_at', '>=', $month)
            ->orderBy('created_at')
            ->chunk($chunk, function (\Illuminate\Support\Collection $list) use ($chunk, &$count) {
                $list->each(function ($record) use ($chunk, &$count) {
                    $activityId = $record->activity_id;
                    $activity = DB::table('lv_activities')->find($activityId);
                    if ( ! $activity) {
                        return;
                    }

                    $isSuccessfulCall = $this->isActivityForSuccessfulCall($activity);

                    DB::table('nurse_care_rate_logs')
                        ->where('id', '=', $record->id)
                        ->update([
                            'patient_user_id'    => $activity->patient_id,
                            'is_successful_call' => $isSuccessfulCall,
                        ]);
                });
                $count += $chunk;
                $this->info("Processed $count records");
            });

        $this->info('Done.');
    }

    private function isActivityForSuccessfulCall($activity): bool
    {
        if ( ! in_array($activity->type, ['Patient Note Creation', 'Patient Note Edit'])) {
            return false;
        }

        $performedAt = Carbon::parse($activity->performed_at);
        $noteIds     = Note
            ::whereBetween('performed_at', [
                $performedAt->copy()->startOfDay(),
                $performedAt->copy()->endOfDay(),
            ])
                ->where('status', '=', Note::STATUS_COMPLETE)
                ->where('author_id', '=', $activity->logger_id)
                ->where('patient_id', '=', $activity->patient_id)
                ->pluck('id');

        $hasSuccessfulCall = false;
        if ( ! empty($noteIds)) {
            $hasSuccessfulCall = Call::whereIn('note_id', $noteIds)
                ->where('status', '=', Call::REACHED)
                ->count() > 0;
        }

        return $hasSuccessfulCall;
    }
}
