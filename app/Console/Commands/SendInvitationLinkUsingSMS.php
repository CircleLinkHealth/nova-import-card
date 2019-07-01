<?php

namespace App\Console\Commands;

use App\Services\SurveyInvitationLinksService;
use App\Services\TwilioClientService;
use App\User;
use Illuminate\Console\Command;
use Twilio\Exceptions\TwilioException;

class SendInvitationLinkUsingSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite:sms {userId} {phoneNumber?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS with invitation link to HRA';

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
     */
    public function handle(SurveyInvitationLinksService $service, TwilioClientService $twilioService)
    {
        $userId = $this->argument('userId');

        $user   = User
            ::with([
                'phoneNumbers',
                'surveyInstances' => function ($instance) {
                    $instance->current();
                },
            ])
            ->where('id', '=', $userId)
            ->first();

        if ( ! $user) {
            $this->warn("Could not find user with id $userId");

            return;
        }

        $url = $service->createAndSaveUrl($userId);

        $phoneNumber = $this->argument('phoneNumber');
        if ( ! $phoneNumber) {
            $phoneNumber = $user->phoneNumbers->first();
        }

        if ( ! $phoneNumber) {
            $this->warn("Could not find a phone number for user $userId");

            return;
        }

        try {
            $twilioService->sendSMS($phoneNumber, "TEST URL: $url");
            $this->info("Done");
        } catch (TwilioException $e) {
            $this->error($e->getMessage());
        }

    }
}
