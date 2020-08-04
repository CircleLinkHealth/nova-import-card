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
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fuck:kolos';

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
        $practiceId = Practice::whereName('calvary-medical-clinic')->firstOrFail()->id;
        $enrollees  = collect();
        Enrollee::with('user')
            ->where('practice_id', $practiceId)
            ->chunk(3, function ($enrolleesModel) use (&$enrollees) {
                $enrollees[] = $enrolleesModel;
            });

        if (empty($enrollees)) {
            $this->warn("No completed Enrolees exist for pratice [$practiceId]. Nothing updated");

            return;
        }

        $survey = Helpers::getEnrolleeSurvey();

        if (empty($survey)) {
            $this->warn("Survey named 'Enrollees' not found!");

            return;
        }

        $surveyInstance = Helpers::getCurrentYearEnrolleeSurveyInstance();

        if (empty($surveyInstance)) {
            $this->warn("Survey Instance for [$survey->id] not found!");

            return;
        }

        foreach ($enrollees->flatten() as $enrollee) {
            if (Helpers::hasCompletedSelfEnrollmentSurvey($enrollee->user)) {
                $data = [
                    'enrollable_id'      => $enrollee->user->id,
                    'survey_instance_id' => $surveyInstance->id,
                ];

                EnrollableSurveyCompleted::dispatch($data);
            }
        }
    }
}
