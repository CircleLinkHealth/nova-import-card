<?php

namespace App\Console\Commands;

use App\Console\Traits\DryRunnable;
use App\NotifiableUser;
use App\Notifications\SurveyInvitationLink;
use App\Services\SurveyInvitationLinksService;
use App\Services\TwilioClientService;
use App\Survey;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInvitationLinkUsingSMS extends Command
{
    use DryRunnable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite:sms {userId} {phoneNumber?} {forYear?} {{--dry-run}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS with invitation link to HRA.';

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
    public function handle(SurveyInvitationLinksService $service, TwilioClientService $twilioService)
    {
        $userId     = $this->argument('userId');
        $surveyName = Survey::HRA;

        $user = User
            ::with([
                'phoneNumbers',
                'patientInfo',
                'primaryPractice',
                'billingProvider',
                'surveyInstances' => function ($query) use ($surveyName) {
                    $query->ofSurvey($surveyName)->current();
                },
            ])
            ->where('id', '=', $userId)
            ->first();

        if ( ! $user) {
            $this->warn("Could not find user with id $userId");

            return;
        }

        $forYear = $this->argument('forYear');
        if ( ! $forYear) {
            $forYear = Carbon::now()->year;
        }

        try {
            $url = $service->createAndSaveUrl($user, $surveyName, $forYear, true);
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

        $notifiableUser = new NotifiableUser($targetNotifiable, null, $phoneNumber);
        $invitation     = new SurveyInvitationLink($url, $surveyName, 'sms');

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
