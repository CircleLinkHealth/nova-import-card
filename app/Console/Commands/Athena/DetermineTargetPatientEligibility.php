<?php

namespace App\Console\Commands\Athena;

use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
use Illuminate\Console\Command;

class DetermineTargetPatientEligibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:DetermineTargetPatientEligibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DetermineEnrollmentEligibility $athenaApi)
    {
        parent::__construct();

        $this->service = $athenaApi;
    }



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
