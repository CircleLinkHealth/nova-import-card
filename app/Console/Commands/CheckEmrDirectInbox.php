<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Core\Contracts\DirectMail;
use Illuminate\Console\Command;

class CheckEmrDirectInbox extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check EMR Direct Mailbox';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emrDirect:checkInbox';
    /**
     * @var DirectMail
     */
    private $directMail;

    /**
     * Create a new command instance.
     */
    public function __construct(DirectMail $directMail)
    {
        parent::__construct();

        $this->directMail = $directMail;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $address = config('services.emr-direct.user');
        if ($address) {
            $this->warn('Checking EMR Direct Inbox.'." Address: $address");
            $this->directMail->receive($address);
            $this->comment('Checked EMR Direct Inbox.'." Address: $address");
        }

        $address = config('services.emr-direct.test_user');
        if ($address) {
            $this->warn('Checking Test EMR Direct Inbox.'." Address: $address");
            $this->directMail->receive($address);
            $this->comment('Checked Test EMR Direct Inbox.'." Address: $address");
        }
    }
}
