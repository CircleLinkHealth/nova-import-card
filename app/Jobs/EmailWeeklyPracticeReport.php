<?php

namespace App\Jobs;

use App\Notifications\WeeklyPracticeReport;
use App\Practice;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Maknz\Slack\Facades\Slack;

class EmailWeeklyPracticeReport implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $practice;
    protected $startRange;
    protected $endRange;
    protected $testerEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Practice $practice, $startRange, $endRange, $testerEmail)
    {
        $this->practice = $practice;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->testerEmail = $testerEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->practice->weekly_report_recipients) {
            return;
        }

        $organizationSummaryRecipients = $this->practice->getWeeklyReportRecipientsArray();

        if ($this->testerEmail) {
            $organizationSummaryRecipients = [$this->testerEmail];
        }

        $subjectPractice = $this->practice->display_name . '\'s CCM Weekly Summary';

        //get Range Summary for this week, and for the other sections get month to date
        $practiceData = (new SalesByPracticeReport(
            $this->practice,
            SalesByPracticeReport::SECTIONS,
            $this->startRange->copy(),
            $this->endRange->copy()
        ))->data(true);

        $practiceData['name'] = $this->practice->display_name;
        $practiceData['start'] = $this->startRange;
        $practiceData['end'] = $this->endRange;
        $practiceData['isEmail'] = true;

        foreach ($organizationSummaryRecipients as $recipient) {
            $user = User::where('email', $recipient)->first();

            if (!$user) {
                $user = (new User)->forceFill([
                    'name' => $recipient,
                    'email' => $recipient,
                ]);
            }

            $user->notify(new WeeklyPracticeReport($practiceData, $subjectPractice));
        }
    }
}
