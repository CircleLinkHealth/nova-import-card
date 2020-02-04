<?php

namespace CircleLinkHealth\Eligibility\Jobs;

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
     * Create a new job instance.
     *
     * @param int $practiceId
     * @param Carbon $date
     */
    public function __construct(int $practiceId, Carbon $date)
    {
        $this->practiceId = $practiceId;
        $this->date       = $date;
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
                                 30,
                                 function ($users) {
                                     foreach ($users as $user) {
                                         $pms = $user->patientSummaries->first();
                    
                                         if ((bool) $this->option('from-scratch')) {
                                             $pms->reset();
                                             $pms->save();
                                         }
                    
                                         if ((bool) $this->option('reset-actor')) {
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
