<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class GetNumberOfBhiChargeablePatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:GetNumberOfBhiChargeablePatients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Returns the number of bhi chargeable patients';

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
     * @return mixed
     */
    public function handle()
    {
        $count = User::isBhiChargeable()->count();
        $this->info($count);
    }
}
