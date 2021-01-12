<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class AddProvidersToSearchDriver extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from User table for Providers to search driver.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:import-providers';

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
        User::ofType('provider')->ofActiveBillablePractice()->searchable();
    }
}
