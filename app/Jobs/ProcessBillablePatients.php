<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\SharedModels\Repositories\BillablePatientsEloquentRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBillablePatients implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var bool
     */
    protected $autoAttest;
    /**
     * @var Carbon
     */
    protected $date;
    /**
     * @var bool
     */
    protected $fromScratch;
    /**
     * @var int
     */
    protected $practiceId;
    /**
     * @var bool
     */
    protected $resetActor;

    /**
     * Create a new job instance.
     */
    public function __construct(int $practiceId, Carbon $date, bool $fromScratch, bool $resetActor, bool $autoAttest)
    {
        $this->practiceId  = $practiceId;
        $this->date        = $date;
        $this->fromScratch = $fromScratch;
        $this->resetActor  = $resetActor;
        $this->autoAttest  = $autoAttest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(BillablePatientsEloquentRepository $billablePatientsRepo)
    {
        $billablePatientsRepo->billablePatients($this->practiceId, $this->date)
            ->chunk(
                100,
                function ($users) {
                    foreach ($users as $user) {
                        $pms = $user->patientSummaries->first();

                        if ($this->fromScratch) {
                            $pms->reset();
                            $pms->save();
                        }

                        if ($this->resetActor) {
                            $pms->actor_id = null;
                            $pms->save();
                        }

                        if ($this->autoAttest) {
                            $pms->autoAttestConditionsIfYouShould();
                        }

                        ProcessApprovableBillablePatientSummary::dispatch(
                            $pms
                        );
                    }
                }
            );
    }
}
