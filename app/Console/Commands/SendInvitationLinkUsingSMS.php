<?php

namespace App\Console\Commands;

use App\Console\Traits\DryRunnable;
use App\NotifiableUser;
use App\Notifications\SurveyInvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\Services\TwilioClientService;
use App\Survey;
use App\User;
use Illuminate\Console\Command;

class SendInvitationLinkUsingSMS extends Command
{
    use DryRunnable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite:sms {userId} {surveyName} {phoneNumber?} {{--dry-run}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS with invitation link to HRA or Vitals.';

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
     * @param TwilioClientService $twilioService
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(SurveyInvitationLinksService $service)
    {
        $userId     = $this->argument('userId');
        $surveyName = $this->argument('surveyName');
        if ( ! ($surveyName === Survey::HRA || $surveyName === Survey::VITALS)) {
            $hra    = Survey::HRA;
            $vitals = Survey::VITALS;
            $this->warn("surveyName must be $hra or $vitals");

            return;
        }

        $user = User
            ::with([
                'awvAppointments',
                'phoneNumbers',
                'patientInfo',
                'primaryPractice',
                'billingProvider',
                'surveyInstances' => function ($query) use ($surveyName) {
                    $query->ofSurvey($surveyName)->mostRecent();
                },
            ])
            ->where('id', '=', $userId)
            ->first();

        if ( ! $user) {
            $this->warn("Could not find user with id $userId");

            return;
        }

        $appointment = optional($user->latestAwvAppointment())->appointment;

        try {
            $url = $service->createAndSaveUrl($user, $surveyName, true);
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return;
        }

        $phoneNumber = $this->argument('phoneNumber');
        if ( ! $phoneNumber) {
            $phoneNumber = $user->phoneNumbers->first();
        }

        if ( ! $phoneNumber) {
            $this->warn("Could not find a phone number for user $userId");

            return;
        }

        /** @var User $targetNotifiable */
        $targetNotifiable = User::find($userId);

        if ( ! $targetNotifiable) {
            throw new \Exception("Could not find user[$phoneNumber] in the system.");
        }

        //in case notifiable user is not the patient
        $providerFullName = "PROVIDER";
        if ( ! $targetNotifiable->is($user)) {
            $practiceName = $user->primaryPractice->display_name;

            $billingProviderUser = $user->billingProviderUser();
            if ($billingProviderUser) {
                $providerFullName = $billingProviderUser->getFullName();
            }
        } else {
            $practiceName        = $targetNotifiable->primaryPractice->display_name;
            $billingProviderUser = $targetNotifiable->billingProviderUser();
            if ($billingProviderUser) {
                $providerFullName = $billingProviderUser->getFullName();
            }
        }

        $notifiableUser = new NotifiableUser($targetNotifiable, null, $phoneNumber);
        $invitation     = new SurveyInvitationLink($url, $surveyName, 'sms', $practiceName, $providerFullName,
            $appointment);

        try {
            if ($this->isDryRun()) {
                $text = $invitation->toTwilio($notifiableUser);
                $this->info("SMS[$phoneNumber] -> $text->content");
            } else {
                $notifiableUser->notify($invitation);
                $this->info('Sending notification');
            }
            $this->info("Done");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }
}
