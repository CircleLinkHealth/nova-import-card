<?php

namespace App\Console\Commands;

use App\Jobs\EmailWeeklyPracticeReport;
use App\Jobs\EmailWeeklyProviderReport;
use App\Practice;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use App\Reports\Sales\Provider\SalesByProviderReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maknz\Slack\Facades\Slack;

class EmailWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:weeklyReports {email?}';

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

        $startRange = Carbon::now()->setTime(0, 0, 0)->subWeek();
        $endRange = Carbon::now()->setTime(0, 0, 0);

        foreach ($this->activePractices as $practice) {
            dispatch(new EmailWeeklyPracticeReport($practice, $startRange, $endRange, $testerEmail));
            dispatch(new EmailWeeklyProviderReport($practice, $startRange, $endRange, $testerEmail));
        }
    }
}
