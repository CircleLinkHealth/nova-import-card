<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class GetNumberOfCcmPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:GetNumberOfCcmPatients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Returns the number ccm patients';

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
        $count = User::whereHas('ccdProblems', function ($q) {
                         $q->where('is_monitored', 1)
                           ->whereHas('cpmProblem', function ($cpm) {
                               return $cpm->where('is_behavioral', 0);
                           });
                     })->count();

        $this->info("$count");
    }
}
