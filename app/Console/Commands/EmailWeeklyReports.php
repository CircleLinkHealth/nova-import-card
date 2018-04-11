<?php

namespace App\Console\Commands;

use App\Jobs\EmailWeeklyPracticeReport;
use App\Jobs\EmailWeeklyProviderReport;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EmailWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:weeklyReports {--practice} {--provider} {testerUserId?} {practiceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emails weekly practice reports to distributors and providers. If a tester user id is passed to the command, all emails will be sent to that user (ie. useful for testing)';

    protected $activePractices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->activePractices = Practice::active()->get();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tester = null;

        if ($this->argument('testerUserId')) {
            $tester = User::findOrFail($this->argument('testerUserId'));
        }

        if ($this->argument('practiceId')) {
            $onlyForPractice = Practice::find($this->argument('practiceId'));
        }

        $startRange = Carbon::now()->subWeek()->startOfDay();
        $endRange = Carbon::now()->endOfDay();

        if (isset($onlyForPractice)) {
            dispatch(new EmailWeeklyPracticeReport($onlyForPractice, $startRange, $endRange, $tester));

            if ($this->option('provider')) {
                dispatch(new EmailWeeklyProviderReport($onlyForPractice, $startRange, $endRange, $tester));
            }

            return;
        }

        foreach ($this->activePractices as $practice) {
            if ($practice->settings->first() && $practice->settings->first()->email_weekly_report && $this->option('provider')) {
                dispatch(new EmailWeeklyProviderReport($practice, $startRange, $endRange, $tester));
            }

            if ($this->option('practice')) {
                dispatch(new EmailWeeklyPracticeReport($practice, $startRange, $endRange, $tester));
            }
        }
    }
}
