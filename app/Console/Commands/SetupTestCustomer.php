<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tests\Helpers\SetupTestCustomer as SetupTestCustomerTrait;

class SetupTestCustomer extends Command
{
    use SetupTestCustomerTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:setupTestCustomer {count=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Practice, a Location, a Provider and 50 Patients with various ccm_status, careplan(draft), 5 cpmProblems, chargeable service, patientMonthlySummaries and activities. Everything is related to eachother.';

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
        $count = $this->argument('count');


        $this->createTestCustomerData($count);
    }
}
