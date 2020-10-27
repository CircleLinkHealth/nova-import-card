<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\PostmarkCallbackNotificationTest;
use App\Notifications\SendGridTestNotification;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Facades\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;

class SendTestEmailToSendGrid extends Command
{
    use Queueable;
    const DEFAULT_POSTMARK_INBOUND_ADDRESS = 'ce336c4be369b05746140c3478913fbd@inbound.postmarkapp.com';

    const POSTMARK_INBOUND_ADDRESS = 'postmark_inbound_address';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to SendGrid';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:test-email {email?} {--callback-mail}';
    /**
     * @var array|string|null
     */
    private $email;

    private bool $isCallbackMail;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $x                    = $this->getPostmarkInboundAddress();
        $this->isCallbackMail = (bool) $this->option('callback-mail');
        $this->email          = $this->isCallbackMail ? self::POSTMARK_INBOUND_ADDRESS : $this->argument('email');

        if ($this->isCallbackMail) {
            try {
                $anonymous = $this->sendToAnonymous();
                $anonymous->notifyNow(new PostmarkCallbackNotificationTest());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            $this->info('Done');

            return;
        }

        if ( ! is_null($this->email)) {
            return $this->sendTestNotification();
        }

        $this->error('Missing email argument');
    }

    /**
     * @return string|string[]|void
     */
    private function getPostmarkInboundAddress()
    {
        $config = AppConfig::pull(self::POSTMARK_INBOUND_ADDRESS, '');

        if (empty($config)) {
            $defaultAddress = self::POSTMARK_INBOUND_ADDRESS;
            $this->warn("Please set $defaultAddress in Configuration panel.");

            return;
        }

        return $config;
    }

    private function sendTestNotification()
    {
        $anonymous = $this->sendToAnonymous();
        $anonymous->notifyNow(new SendGridTestNotification());
        $this->info('Done');
    }

    /**
     * @return \Illuminate\Notifications\AnonymousNotifiable
     */
    private function sendToAnonymous()
    {
        return Notification::route('mail', $this->email);
    }
}
