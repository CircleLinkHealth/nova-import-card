<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\PostmarkTestCallbackNotification;
use App\Notifications\SendGridTestNotification;
use App\Services\Postmark\PostmarkCallbackMailService;
use CircleLinkHealth\Core\Facades\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;

class SendTestEmailToSendGrid extends Command
{
    use Queueable;
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
        $this->email          = $this->argument('email') ?: null;
        $this->isCallbackMail = (bool) $this->option('callback-mail');

        if ($this->isCallbackMail) {
            
            try {
//                (new PostmarkCallbackMailService())->createCallbackNotification(); // Rename this after future.
                $anonymous = $this->sendToAnonymous();
                $anonymous->notifyNow(new PostmarkTestCallbackNotification());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            return $this->info('Done');
        }

        if ( ! is_null($this->email)) {
            return $this->sendTestNotification();
        }

        $this->error('Missing email argument');
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
