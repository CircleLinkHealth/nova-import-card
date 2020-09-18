<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use CircleLinkHealth\SharedModels\Entities\Call;
use App\Note;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Console\Command;

class FixSuccessfulCallsOfCareRateLogs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix care rate logs';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'care-rate-logs:fix-logs {startDate} {endDate}';

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
     * @return mixed
     */
    public function handle()
    {
        $totalCount    = 0;
        $totalModified = 0;
        $startDateStr  = $this->argument('startDate');
        $startDate     = Carbon::parse($startDateStr);
        $endDateStr    = $this->argument('endDate');
        $endDate       = Carbon::parse($endDateStr);

        NurseCareRateLog::with(['activity'])
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay()->toDateTimeString(),
                $endDate->copy()->startOfDay()->toDateTimeString(),
            ])
            ->chunk(100, function ($items) use (&$totalCount, &$totalModified) {
                $items->each(function ($item) use (&$totalCount, &$totalModified) {
                    ++$totalCount;
                    if ( ! $item->activity) {
                        return;
                    }
                    if ( ! $this->isActivityForSuccessfulCall($item->activity)) {
                        return;
                    }

                    $item->is_successful_call = 1;
                    $item->save();
                    ++$totalModified;
                });
            });

        $this->info("Out of $totalCount nurse_care_rate_logs entries, $totalModified were modified.");
    }

    /**
     * Not the same as in
     * {@link AlternativeCareTimePayableCalculator::isActivityForSuccessfulCall}.
     */
    private function isActivityForSuccessfulCall(Activity $activity): bool
    {
        if ( ! in_array($activity->type, ['Patient Note Creation', 'Patient Note Edit'])) {
            return false;
        }

        $performedAt = Carbon::parse($activity->performed_at);
        $noteIds     = Note
            ::where(function ($q) use ($performedAt) {
                $q->whereBetween('performed_at', [
                    $performedAt->copy()->startOfDay(),
                    $performedAt->copy()->endOfDay(),
                ])->orWhereBetween('updated_at', [
                    $performedAt->copy()->startOfDay(),
                    $performedAt->copy()->endOfDay(),
                ]);
            })
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
