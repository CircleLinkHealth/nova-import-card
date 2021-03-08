<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction;

use CircleLinkHealth\SelfEnrollment\Console\Commands\CommandHelpers;
use CircleLinkHealth\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class MakeSurveyOnlyUsersForEnrollees extends Command
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
    protected $signature = 'enrollees:create-survey-only-users {practiceId} {limit?} {enrolleeIds?*}';

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
        $enrolleeIds = CommandHelpers::getEnrolleeIds( $this->argument('enrolleeIds'));
        $limit = $this->argument('limit') ?? null;

        Enrollee::whereNull('user_id')
            ->where('practice_id', $this->argument('practiceId'))
            ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
            ->whereNotNull('provider_id')
            ->when($enrolleeIds, function ($enrollees) use ($enrolleeIds){
                $enrollees->whereIn('id', $enrolleeIds);
            })
            ->when($limit, function ($query) use($limit){
                $query->limit(intval($limit));
            })
            ->select('id')
            ->chunk(100, function ($enrollees) {
                $enrollees->each(function ($e) {
                    $this->warn("Create user from enrollee {$e->id}");
                    CreateSurveyOnlyUserFromEnrollee::dispatch($e);
                });
            });
    }
}
