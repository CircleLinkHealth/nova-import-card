<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Contracts\DirectMail;
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
        $this->warn('Checking EMR Direct Inbox.');
        $this->directMail->receive();
        $this->comment('Checked EMR Direct Inbox.');
    }
}
