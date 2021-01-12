<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\WeeklyPracticeReport;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class EmailWeeklyPracticeReport implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected $endRange;

    protected $practice;
    protected $startRange;
    protected $tester;

    /**
     * Create a new job instance.
     *
     * @param $startRange
     * @param $endRange
     */
    public function __construct(Practice $practice, $startRange, $endRange, User $tester = null)
    {
        $this->practice   = $practice;
        $this->startRange = $startRange;
        $this->endRange   = $endRange;
        $this->tester     = $tester;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ( ! $this->practice->weekly_report_recipients) {
            return;
        }

        $organizationSummaryRecipients = $this->practice->getWeeklyReportRecipientsArray();

        $subjectPractice = $this->practice->display_name.'\'s CCM Weekly Summary';

        //get Range Summary for this week, and for the other sections get month to date
        $practiceData = (new SalesByPracticeReport(
            $this->practice,
            SalesByPracticeReport::SECTIONS,
            $this->startRange->copy(),
            $this->endRange->copy()
        ))->data(true);

        $practiceData['name']    = $this->practice->display_name;
        $practiceData['start']   = $this->startRange;
        $practiceData['end']     = $this->endRange;
        $practiceData['isEmail'] = true;

        $notification = new WeeklyPracticeReport($practiceData, $subjectPractice);

        if ($this->tester) {
            $this->tester->notify($notification);
        } else {
            foreach ($organizationSummaryRecipients as $recipient) {
                $user = User::whereEmail($recipient)->first();

                if ($user) {
                    $user->notify($notification);
                } else {
                    Notification::route('mail', $recipient)
                        ->notify($notification);
                }
            }
        }
    }
}
