<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class GetNumberOfBhiChargeablePatients extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Returns the number of bhi chargeable patients';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:GetNumberOfBhiChargeablePatients';

    /**
     * Create a new command instance.
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
