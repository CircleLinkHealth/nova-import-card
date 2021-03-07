<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\SelfEnrollment\Database\Seeders\CreateEnrolleesSurveySeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateSelfEnrollmentSurveyCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates DB with Self Enrollment Question and Survey attributes';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:selfEnrollmentSurvey';

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
     * @return void
     */
    public function handle()
    {
        /*
         * Cases that this will work:
         *  1. No Enrollee Questions Data Exists at all.
         *  2. Enrollee Questions Data Exists but survey_questions are missing current survey_instance(instance for current year).
         *
         * You are bout to enter a not great written seeder...
         */
        Artisan::call('db:seed', ['--class' => CreateEnrolleesSurveySeeder::class]);
        $this->info('done');
    }
}
