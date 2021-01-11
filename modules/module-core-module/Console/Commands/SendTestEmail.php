<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Core\Notifications\PostmarkCallbackNotificationTest;
use CircleLinkHealth\Core\Notifications\TestEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;

class SendTestEmail extends Command
{
    use Queueable;
    const POSTMARK_INBOUND_ADDRESS_CONFIG_KEY = 'postmark_inbound_address';
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
        $this->isCallbackMail = (bool) $this->option('callback-mail');
        $this->email          = $this->isCallbackMail ? self::POSTMARK_INBOUND_ADDRESS_CONFIG_KEY : $this->argument('email');

        if ($this->isCallbackMail) {
            try {
                $anonymous = $this->sendAnonymousNotification();
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
        $config = AppConfig::pull(self::POSTMARK_INBOUND_ADDRESS_CONFIG_KEY, '');

        if (empty($config)) {
            $defaultAddress = self::POSTMARK_INBOUND_ADDRESS_CONFIG_KEY;
            $this->warn("Please set $defaultAddress in Configuration panel.");

            return '';
        }

        return $config;
    }

    /**
     * @return \Illuminate\Notifications\AnonymousNotifiable
     */
    private function sendAnonymousNotification()
    {
        return Notification::route('mail', $this->email);
    }

    private function sendTestNotification()
    {
        $anonymous = $this->sendAnonymousNotification();
        $anonymous->notifyNow(new TestEmailNotification());
        $this->info('Done');
    }
}
