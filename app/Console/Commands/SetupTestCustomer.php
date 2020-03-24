<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Traits\SetupTestCustomerTrait;
use Illuminate\Console\Command;

class SetupTestCustomer extends Command
{
    use SetupTestCustomerTrait;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Practice, a Location, a Provider and 50 Patients with various ccm_status, careplan(draft), 5 cpmProblems, chargeable service, patientMonthlySummaries and activities. Everything is related to eachother.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:setupTestCustomer {count=100}';

    /**
     * Create a new command instance.
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
