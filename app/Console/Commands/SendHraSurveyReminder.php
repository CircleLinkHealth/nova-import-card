<?php

namespace App\Console\Commands;

use App\NotifiableUser;
use App\Notifications\SurveyInvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Illuminate\Console\Command;

class SendHraSurveyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:reminder {daysPrior}';

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
        // $notifyClh = $this->option('notifyClh');

        $date      = now()->addDays($daysPrior);
        $dateStart = $date->copy()->startOfDay();
        $dateEnd   = $date->copy()->endOfDay();

        $service = app(SurveyInvitationLinksService::class);

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
            ->each(function (User $user) use ($service) {

                if ($user->surveyInstances->isNotEmpty() &&
                    $user->surveyInstances->first()->pivot->status === SurveyInstance::COMPLETED) {
                    return;
                }

                try {
                    $url = $service->createAndSaveUrl($user, Survey::HRA, true);
                } catch (\Exception $e) {
                    throw $e;
                }

                $practiceName     = optional($user->primaryPractice)->display_name;
                $providerFullName = optional($user->billingProviderUser())->getFullName();
                $appointment      = $user->latestAwvAppointment()->appointment;
                $notifiableUser   = new NotifiableUser($user);
                $notifiableUser->notify(new SurveyInvitationLink($url, Survey::HRA, null, $practiceName,
                    $providerFullName,
                    $appointment));
            });

        /*
         * not doing this, but keeping in case we need it in the near future
        if ($hasSentAtLeastOneReminder && $notifyClh) {
            $param        = [
                'start' => $dateStart->toDateTimeString(),
                'end'   => $dateEnd->toDateTimeString(),
            ];
            $url          = route('patient.list', ['appointment' => json_encode($param)]);
            $notification = new AppointmentsReminderNotification('#awv', $url);
            sendSlackMessage($notification);
        }
        */
    }
}
