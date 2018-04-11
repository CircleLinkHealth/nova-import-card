<?php

namespace App\Console\Commands;

use App\Services\PhiMail\PhiMail;
use Illuminate\Console\Command;

class CheckEmrDirectInbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emrDirect:checkInbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check EMR Direct Mailbox';
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
