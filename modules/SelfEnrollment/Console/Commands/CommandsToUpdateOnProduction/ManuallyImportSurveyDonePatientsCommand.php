<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands\CommandsToUpdateOnProduction;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SelfEnrollment\Jobs\EnrollableSurveyCompleted;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ManuallyImportSurveyDonePatientsCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually import enrollees that have survey completed and triggered status update to consented.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enroll:surveyDoneUser {enrolleeUserIds*}';

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
     * @return int
     */
    public function handle()
    {
        $userIds = $this->argument('enrolleeUserIds');

        if (empty($userIds)) {
           $this->error('enrolleeUserIds required');
           return 0;
        }

        $surveyInstance = Helpers::getCurrentYearEnrolleeSurveyInstance()->id;

        if (empty($surveyInstance)) {
            $this->error('Could not find survey instance for enrollees survey');
            return 0;
        }

        $countUserIds = collect($userIds)->count();

        $this->info("Will attempt to manually import $countUserIds self enrollment users");

        $filteredUserIds = $this->filterSurveyStatusOfUsers($userIds,$countUserIds);

        if ($filteredUserIds->isEmpty()){
            $this->error('Aborting! [$filteredUserIds] is empty');
            return 0;
        }


        $filteredUserIds->each(function ($userId) use($surveyInstance){
            $data = [
                'enrollable_id'      => $userId,
                'survey_instance_id' => $surveyInstance,
            ];

            $this->info("Dispatching EnrollableSurveyCompleted job for user_id $userId");
            EnrollableSurveyCompleted::dispatch($data);
        });


        $this->info("Command finished successfully. Asserting enrollees status is updated...");

        $failedToImportIds = DB::table('enrollees')
            ->whereIn('user_id', $filteredUserIds)
            ->whereNotIn('status', [Enrollee::ENROLLED])
            ->pluck('user_id');

        if ($failedToImportIds->isNotEmpty()){
            $this->info("Command failed to import user ids: [$failedToImportIds]");
        }

        $this->info(" Status in Enrollee updated to ENROLLED for enrollees with user id: [$filteredUserIds].");

        return 0;
    }

    /**
     * @param array $userIds
     * @param int $countUserIdsUnfiltered
     * @return Collection
     */
    public function filterSurveyStatusOfUsers(array $userIds, int $countUserIdsUnfiltered):Collection
    {
        $userIdsFilteredBySurveyAndEnrolleeStatus = DB::table('users_surveys')
            ->whereIn('users_surveys.user_id', $userIds)
            ->where('users_surveys.status', 'completed')
            ->join('enrollees', function ($join){
                $join->on('users_surveys.user_id','=','enrollees.user_id')
                ->where('enrollees.status', '=', Enrollee::CONSENTED)
                ->where('enrollees.auto_enrollment_triggered', '=', true);
            })->pluck('users_surveys.user_id');


        $filteredUserIds = $userIdsFilteredBySurveyAndEnrolleeStatus->diffAssoc(collect($userIds));

        $this->info("{$filteredUserIds->count()} enrollees will be imported out of $countUserIdsUnfiltered");
        $this->info("The following user ids have survey status completed. OK to be imported: $filteredUserIds");

        return $filteredUserIds;
    }
}
