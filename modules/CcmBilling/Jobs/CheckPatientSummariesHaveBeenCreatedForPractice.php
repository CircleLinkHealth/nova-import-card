<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPatientSummariesHaveBeenCreatedForPractice implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $month;

    protected int $practiceId;

    protected LocationProcessorEloquentRepository $repo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId, Carbon $month = null)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    public function getPracticeId(): int
    {
        return $this->practiceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! $this->summariesExistForMonth()) {
            $readableMonth = $this->getMonth()->format('M, Y');
            sendSlackMessage('#billing_alerts', "Summaries have not been created for Practice with ID: {$this->getPracticeId()}, for month: {$readableMonth}");
            ProcessPracticePatientMonthlyServices::dispatch($this->getPracticeId(), $this->getMonth());
        }
    }

    private function repo(): LocationProcessorEloquentRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(LocationProcessorEloquentRepository::class);
        }

        return $this->repo;
    }

    private function summariesExistForMonth(): bool
    {
        $locationIds = Location::where('practice_id', $this->getPracticeId())->pluck('id')->toArray();

        return $this->repo()
            ->patientServices($locationIds, $this->getMonth())
            ->exists();
    }
}
