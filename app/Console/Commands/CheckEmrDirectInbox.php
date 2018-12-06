<?php

namespace App\Console\Commands;

use App\Contracts\DirectMail;
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
    /**
     * @var DirectMail
     */
    private $directMail;
    
    /**
     * Create a new command instance.
     *
     * @param DirectMail $directMail
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
        $this->directMail->receive();
    }
}
