<?php

namespace App\Console\Commands;

use App\Console\Traits\DryRunnable;
use App\Services\SurveyInvitationLinksService;
use App\Services\TwilioClientService;
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

        $forYear = $this->argument('forYear');
        if ( ! $forYear) {
            $forYear = Carbon::now()->year;
        }

        try {
            $url = $service->createAndSaveUrl($userId, $forYear, true);
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

        try {
            if ($this->isDryRun()) {
                $this->info("SMS[$phoneNumber] -> TEST URL: $url");
            } else {
                $twilioService->sendSMS($phoneNumber, "TEST URL: $url");
            }
            $this->info("Done");

        } catch (TwilioException $e) {
            $this->error($e->getMessage());
        }

    }
}
