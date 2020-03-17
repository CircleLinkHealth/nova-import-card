<?php

namespace App\Jobs;

use App\Jobs\AttachBillableProblemsToSummary;
use App\Repositories\BillablePatientsEloquentRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessLastMonthBillablePatients implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var int
     */
    protected $practiceId;
    /**
     * @var Carbon
     */
    protected $date;
    /**
     * @var bool
     */
    protected $fromScratch;
    /**
     * @var bool
     */
    protected $resetActor;
    
    /**
     * Create a new job instance.
     *
     * @param int $practiceId
     * @param Carbon $date
     * @param bool $fromScratch
     * @param bool $resetActor
     */
    public function __construct(int $practiceId, Carbon $date, bool $fromScratch, bool $resetActor)
    {
        $this->practiceId = $practiceId;
        $this->date       = $date;
        $this->fromScratch = $fromScratch;
        $this->resetActor = $resetActor;
    }
    
    /**
     * Execute the job.
     *
     * @param BillablePatientsEloquentRepository $billablePatientsRepo
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
                    
                                         AttachBillableProblemsToSummary::dispatch(
                                             $pms
                                         );
                                     }
                                 }
                             );
    }
}
