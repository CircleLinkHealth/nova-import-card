<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Commands;


use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Console\Commands\ToledoPracticeProviders\UpdateProvidersFromExcel;
use Illuminate\Console\Command;

class UpdateHealthCenterOfSouthEastTexasLocationNames extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Patients who have the practice name as the facility to have the primary location suffixed (Cleveland)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:healthCenterSETexasLocationNames';

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

    }
}