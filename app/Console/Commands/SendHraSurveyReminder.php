<?php

namespace App\Console\Commands;

use App\Notifications\AppointmentsReminderNotification;
use App\Notifications\SurveyInvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\Survey;
use App\User;
use Illuminate\Console\Command;

class SendHraSurveyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:reminder {daysPrior} {--notifyClh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a reminder to AWV patients X days prior their appointment. Also notifies CLH.';

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
        $daysPrior = $this->argument('daysPrior');
        $notifyClh = $this->option('notifyClh');

        $date      = now()->addDays($daysPrior);
        $dateStart = $date->copy()->startOfDay();
        $dateEnd   = $date->copy()->endOfDay();

        $service = app(SurveyInvitationLinksService::class);

        $hasSentAtLeastOneReminder = false;

        User::ofType('participant', false)
            ->whereHas('awvAppointments', function ($q) use ($dateStart, $dateEnd) {
                $q->whereBetween('appointment', [$dateStart, $dateEnd]);
            })
            ->with([
                'surveyInstances' => function ($query) {
                    $query->ofSurvey(Survey::HRA)->mostRecent();
                },
            ])
            ->get()
            ->each(function (User $user) use ($service, &$hasSentAtLeastOneReminder) {
                try {
                    $url = $service->createAndSaveUrl($user, Survey::HRA, true);
                } catch (\Exception $e) {
                    throw $e;
                }
                $hasSentAtLeastOneReminder = true;
                $user->notify(new SurveyInvitationLink($url, Survey::HRA));
            });

        if ($hasSentAtLeastOneReminder && $notifyClh) {
            $param        = [
                'start' => $dateStart->toDateTimeString(),
                'end'   => $dateEnd->toDateTimeString(),
            ];
            $url          = route('patient.list', ['appointment' => json_encode($param)]);
            $notification = new AppointmentsReminderNotification('#awv', $url);
            sendSlackMessage($notification);
        }
    }
}
