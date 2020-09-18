<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class MakeSurveyOnlyUsersForAllExistingEnrollees extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Survey Only users (for AutoEnroll)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:create-survey-only-users {practiceId}';

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
        Enrollee::whereNull('user_id')
            ->where('practice_id', $this->argument('practiceId'))
            ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
            ->select('id')
            ->chunk(100, function ($enrollees) {
                $enrollees->each(function ($e) {
                    $this->warn("Create user from enrollee {$e->id}");
                    CreateSurveyOnlyUserFromEnrollee::dispatch($e);
                });
            });
    }
}
