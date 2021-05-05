<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Console\Commands;


use CircleLinkHealth\Customer\Jobs\CheckPatientRolesAndNotifySlack;
use CircleLinkHealth\Customer\Jobs\EraseTestEnrollees as EraseTestEnrolleesJob;
use Illuminate\Console\Command;

class CheckPatientRoles extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erase all test Enrollees (created by seeder)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:check-roles';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CheckPatientRolesAndNotifySlack::dispatch();
    }
}