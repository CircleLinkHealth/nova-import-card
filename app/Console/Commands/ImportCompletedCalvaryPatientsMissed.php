<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\SelfEnrollment\Helpers;
use App\SelfEnrollment\Jobs\EnrollableSurveyCompleted;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
            ->chunk(100, function ($enrollees) use ($practiceId, $surveyInstance) {
                foreach ($enrollees as $enrollee) {
                    if (Helpers::hasCompletedSelfEnrollmentSurvey($enrollee->user)) {
                        $data = [
                            'enrollable_id'      => $enrollee->user->id,
                            'survey_instance_id' => $surveyInstance->id,
                        ];

                        EnrollableSurveyCompleted::dispatch($data);
                    }
                }
            });
    }
}
