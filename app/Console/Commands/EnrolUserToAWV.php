<?php

namespace App\Console\Commands;

use App\Services\SurveyInvitationLinksService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EnrolUserToAWV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrol:user {userId} {forYear?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrol a user to AWV Surveys (HRA and Vitals).';

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
     * @param SurveyInvitationLinksService $service
     *
     * @return mixed
     */
    public function handle(SurveyInvitationLinksService $service)
    {
        $userId = $this->argument('userId');

        $user = User
            ::with([
                'surveyInstances' => function ($query) {
                    $query->mostRecent();
                },
            ])
            ->where('id', '=', $userId)
            ->first();

        if (! $user) {
            $this->warn("Could not find user with id $userId");

            return;
        }

        $forYear = $this->argument('forYear');
        if (! $forYear) {
            $forYear = Carbon::now()->year;
        }

        //exit if enrolled for both HRA and Vitals
        if ($user->surveyInstances->count() > 1) {
            $this->warn("User[$userId] is already enrolled to AWV for year $forYear");

            return;
        }

        try {
            $service->enrolUser($user, $forYear);
            $this->info('Done');
        } catch (\Exception $e) {
            $this->warn($e);
        }
    }
}
