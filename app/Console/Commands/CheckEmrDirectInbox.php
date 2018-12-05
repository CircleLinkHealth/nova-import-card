<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\PhiMail\PhiMail;
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
    private $phiMail;

    /**
     * Create a new command instance.
     *
     * @param PhiMail $phiMail
     */
    public function __construct(PhiMail $phiMail)
    {
        parent::__construct();

        $this->phiMail = $phiMail;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->phiMail->receive();
    }
}
