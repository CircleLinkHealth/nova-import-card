<?php

namespace App\Console\Commands;

use App\Services\AthenaAPI\Service;
use Illuminate\Console\Command;

class GetPatientIdFromAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:GetPatientIdFromAppointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve patient Ids from past booked appointments from the Athena API';


    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Service $athenaApi)
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
