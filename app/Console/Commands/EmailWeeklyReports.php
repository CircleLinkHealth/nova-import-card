<?php

namespace App\Console\Commands;

use App\Jobs\EmailWeeklyPracticeReport;
use App\Jobs\EmailWeeklyProviderReport;
use App\Practice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Maknz\Slack\Facades\Slack;

class EmailWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:weeklyReports {--practice} {--provider} {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emails weekly practice reports to distributors and providers. If email is passed to the command, all emails will be sent to that email (ie. useful for testing)';

    protected $activePractices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->activePractices = Practice::active();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $testerEmail = null;

        if ($this->argument('email')) {
            $testerEmail = $this->argument('email');
        }

        $startRange = Carbon::now()->subWeek()->startOfDay();
        $endRange = Carbon::now()->endOfDay();

        foreach ($this->activePractices as $practice) {
            if (!$practice->settings[0]->email_weekly_report) {
                continue;
            }

            if ($this->option('practice')) {
                dispatch(new EmailWeeklyPracticeReport($practice, $startRange, $endRange, $testerEmail));
            }

            if ($this->option('provider')) {
                dispatch(new EmailWeeklyProviderReport($practice, $startRange, $endRange, $testerEmail));
            }
        }
    }
}
