<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPatientSummariesHaveBeenCreatedForPractice implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $month;

    protected int $practiceId;

    protected PracticeProcessorRepository $repo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId, Carbon $month)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month;
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
            sendSlackMessage('#cpm_general_alerts', "Summaries have not been created for Practice with ID: {$this->getPracticeId()}, for month: {$readableMonth}");
            ProcessPracticePatientMonthlyServices::dispatch($this->getPracticeId(), $this->getMonth());
        }
    }

    private function repo(): PracticeProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PracticeProcessorRepository::class);
        }

        return $this->repo;
    }

    private function summariesExistForMonth(): bool
    {
        return $this->repo()
            ->patientServices($this->getPracticeId(), $this->getMonth())
            ->exists();
    }
}
