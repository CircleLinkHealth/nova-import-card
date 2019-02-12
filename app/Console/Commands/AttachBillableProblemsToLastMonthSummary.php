<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\AttachBillableProblemsToSummary;
use App\Practice;
use App\Repositories\BillablePatientsEloquentRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttachBillableProblemsToLastMonthSummary extends Command
{
    protected $billablePatientsRepo;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attach 2 billable problems to each of last month\'s summaries';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summaries:attach-problems-to-last-month
                                {date? : the month we are calculating for in YYYY-MM-DD}
                                {practiceIds? : comma separated. leave empty to recalculate for all}
                                {--reset : unlock the month and delete actor id, and problems}
                                ';
    
    /**
     * Create a new command instance.
     */
    public function __construct(BillablePatientsEloquentRepository $billablePatientsRepo)
    {
        parent::__construct();
        $this->billablePatientsRepo = $billablePatientsRepo;
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $practiceIds = array_filter(explode(',', $this->argument('practiceIds')));
        
        $datePassed = $this->argument('date');
        $month      = $datePassed
            ? Carbon::parse($datePassed)->startOfMonth()
            : Carbon::now()->subMonth()->startOfMonth();
        
        Practice::active()
                ->when(
                    $practiceIds,
                    function ($q) use ($practiceIds) {
                        $q->whereIn('id', $practiceIds);
                    }
                )
                ->chunk(
                    5,
                    function ($practices) use ($month) {
                        foreach ($practices as $practice) {
                            $this->comment("BEGIN processing $practice->display_name for {$month->toDateString()}");
                    
                            $this->billablePatientsRepo->billablePatients($practice->id, $month)
                                                       ->chunk(
                                                           50,
                                                           function ($users) {
                                                               foreach ($users as $user) {
                                                                   $pms = $user->patientSummaries->first();
                                
                                                                   if ( ! ! $this->option('reset')) {
                                                                       $pms->reset();
                                                                       $pms->save();
                                                                   }
                                
                                                                   AttachBillableProblemsToSummary::dispatch(
                                                                       $pms
                                                                   );
                                                               }
                                                           }
                                                       );
                    
                            $this->output->success(
                                "END processing $practice->display_name for {$month->toDateString()}"
                            );
                        }
                    }
                );
    }
}
