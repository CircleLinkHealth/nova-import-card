<?php

namespace App\Console\Commands;

use App\Activity;
use App\PageTimer;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailRNDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurses:emailDailyReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
     * @return mixed
     */
    public function handle()
    {
        $nurses = User::ofType('care-center')->get();

        $counter = 0;
        $emailsSent = [];

        foreach ($nurses as $nurse) {
            $activityTime = Activity::createdBy($nurse)
                ->createdToday()
                ->sum('duration');

            $systemTime = PageTimer::where('provider_id', $nurse->id)
                ->createdToday()
                ->sum('billable_duration');

            $totalMonthSystemTimeSeconds = PageTimer::where('provider_id', $nurse->id)
                ->createdThisMonth()
                ->sum('billable_duration');

            if ($systemTime == 0) {
                continue;
            }

            if ($nurse->nurseInfo->hourly_rate < 1
                && $nurse->nurseInfo != 'active'
            ) {
                continue;
            }

            $performance = round((float)($activityTime / $systemTime) * 100);

            $totalTimeInSystemToday = secondsToHMS($systemTime);

            $totalTimeInSystemThisMonth = secondsToHMS($totalMonthSystemTimeSeconds);

            $totalEarningsThisMonth = round((float)($totalMonthSystemTimeSeconds * $nurse->nurseInfo->hourly_rate / 60 / 60),
                2);

            $data = [
                'name'                       => $nurse->fullName,
                'performance'                => $performance,
                'totalEarningsThisMonth'     => $totalEarningsThisMonth,
                'totalTimeInSystemToday'     => $totalTimeInSystemToday,
                'totalTimeInSystemThisMonth' => $totalTimeInSystemThisMonth,
            ];

            $recipients = [
                $nurse->email,
                //                                'raph@circlelinkhealth.com',
                //                            'mantoniou@circlelinkhealth.com',
            ];

            $subject = 'CircleLink Daily Time Report';

            Mail::send('emails.nurseDailyReport', $data, function ($message) use
            (
                $recipients,
                $subject
            ) {
                $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                $message->to($recipients)->subject($subject);
            });

            $emailsSent[] = [
                'nurse' => $nurse->fullName,
                'email' => $nurse->email,
            ];

            $counter++;
        }

        $this->table([
            'nurse',
            'email',
        ], $emailsSent);

        $this->info("$counter email(s) sent.");
    }
}
