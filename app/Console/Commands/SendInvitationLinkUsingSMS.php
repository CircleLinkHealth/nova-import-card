<?php

namespace App\Console\Commands;

use App\Console\Traits\DryRunnable;
use App\Services\SurveyInvitationLinksService;
use App\Services\TwilioClientService;
use App\Survey;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Twilio\Exceptions\TwilioException;

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
        $userId = $this->argument('userId');

        $user = User
            ::with([
                'phoneNumbers',
                'patientInfo',
                'primaryPractice',
                'billingProvider',
                'surveyInstances' => function ($query) {
                    $query->ofSurvey(Survey::HRA)->current();
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
            $url = $service->createAndSaveUrl($user, $forYear, true);
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

        $text = $service->getSMSText($user, $url);

        try {
            if ($this->isDryRun()) {
                $this->info("SMS[$phoneNumber] -> $text");
            } else {
                $twilioService->sendSMS($phoneNumber, $text);
            }
            $this->info("Done");

        } catch (TwilioException $e) {
            $this->error($e->getMessage());
        }

    }
}
