<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SelfEnrollment\Jobs\EnrollableSurveyCompleted;
use Illuminate\Console\Command;

class ManuallyImportSurveyDonePatientsCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enroll:surveyDoneUser {userId}';

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
        $userId = $this->argument('userId');
        if (is_null($userId)) {
            return $this->info('User id of survey done user is required');
        }

        $surveyInstance = Helpers::getCurrentYearEnrolleeSurveyInstance()->id;

        if (empty($surveyInstance)) {
            return $this->info('Could not find survey instance for enrollees survey');
        }

        $data = [
            'enrollable_id'      => $this->argument('userId'),
            'survey_instance_id' => $surveyInstance,
        ];

        EnrollableSurveyCompleted::dispatch($data);
    }
}
