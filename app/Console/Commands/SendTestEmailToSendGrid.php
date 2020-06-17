<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\SendGridTestNotification;
use CircleLinkHealth\Core\Facades\Notification;
use Illuminate\Console\Command;

class SendTestEmailToSendGrid extends Command
{
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
    protected $signature = 'send:test-email {email}';

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
        $email     = $this->argument('email');
        $anonymous = Notification::route('mail', $email);
        $anonymous->notifyNow(new SendGridTestNotification());
        $this->info('Done');
    }
}
