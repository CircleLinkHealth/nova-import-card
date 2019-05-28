<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\EmailWeeklyPracticeReport;
use App\Jobs\EmailWeeklyProviderReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class EmailWeeklyReports extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emails weekly practice reports to distributors and providers. If a tester user id is passed to the command, all emails will be sent to that user (ie. useful for testing)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:weeklyReports {--practice} {--provider} {testerUserId?} {practiceId?} {endDate? : End date in YYYY-MM-DD. The report will be produced from a week before endDate, up to endDate}';

    /**
     * Create a new command instance.
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
        $tester = null;

        if ($this->argument('testerUserId')) {
            $tester = User::findOrFail($this->argument('testerUserId'));
        }

        if ($this->argument('practiceId')) {
            $onlyForPractice = Practice::findOrFail($this->argument('practiceId'));
        }

        $endDate = $this->argument('endDate') ?? null;

        $endRange = $endDate
            ? Carbon::parse($endDate)
            : Carbon::now()->endOfDay();

        $startRange = $endRange->copy()->subWeek()->startOfDay();

        if (isset($onlyForPractice)) {
            dispatch(new EmailWeeklyPracticeReport($onlyForPractice, $startRange, $endRange, $tester));

            if ($this->option('provider')) {
                dispatch(new EmailWeeklyProviderReport($onlyForPractice, $startRange, $endRange, $tester));
            }

            return;
        }

        foreach (Practice::active()->get() as $practice) {
            if ($practice->settings->first() && $practice->settings->first()->email_weekly_report && $this->option('provider')) {
                dispatch(new EmailWeeklyProviderReport($practice, $startRange, $endRange, $tester));
            }

            if ($this->option('practice')) {
                dispatch(new EmailWeeklyPracticeReport($practice, $startRange, $endRange, $tester));
            }
        }
    }
}
