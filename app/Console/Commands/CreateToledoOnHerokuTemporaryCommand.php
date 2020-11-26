<?php

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Database\Seeders\GenerateToledoClinicLetter;
use Illuminate\Console\Command;

class CreateToledoOnHerokuTemporaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:toledo-letter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        (new GenerateToledoClinicLetter())->run();
    }
}
