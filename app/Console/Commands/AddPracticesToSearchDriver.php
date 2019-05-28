<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Console\Command;

class AddPracticesToSearchDriver extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from Practices table to search driver.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:import-practices';

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
        Practice::activeBillable()->searchable();
    }
}
