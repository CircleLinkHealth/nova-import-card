<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SelfEnrollment\Jobs\EnrollableSurveyCompleted;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class ImportCompletedCalvaryPatientsMissed extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Calvary practice Enrolees that Completed Survey and did not get imported';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:calvary-survey-completed';

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
        $practiceId     = Practice::whereName('calvary-medical-clinic')->firstOrFail()->id;
        $surveyInstance = Helpers::getCurrentYearEnrolleeSurveyInstance();

        Enrollee::with([
            'user' => function ($q) {
                $q->without(['roles', 'perms']);
            },
        ])->has('user')
            ->where('practice_id', $practiceId)
            ->chunk(100, function ($enrollees) use ($surveyInstance) {
                foreach ($enrollees as $enrollee) {
                    if (Helpers::hasCompletedSelfEnrollmentSurvey($enrollee->user)) {
                        EnrollableSurveyCompleted::dispatch([
                            'enrollable_id'      => $enrollee->user->id,
                            'survey_instance_id' => $surveyInstance->id,
                        ]);
                    }
                }
            });
    }
}
