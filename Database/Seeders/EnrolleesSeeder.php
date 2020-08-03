<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Seeder;

class EnrolleesSeeder extends Seeder
{
    use SeedEligibilityJobsForEnrollees;

    /**
     * Run the database seeds.
     */
    public function run()
    {
        ini_set('max_execution_time', 300);

        $this->command->info('Seeding Enrollees.');

        $practice = Practice::where('name', 'demo')
            ->where('is_demo', true)
            ->first();

        if (app()->environment(['testing', 'review', 'local']) && ! $practice) {
            $practice = factory(Practice::class)->create();
        }

        $enrollees = factory(Enrollee::class, 10)->create();

        $this->command->info('Enrollees created.');

        $this->command->info('Seeding Eligibility Jobs for Enrollees.');

        $this->seedEligibilityJobs($enrollees, $practice);
    }
}
