<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use Illuminate\Console\Command;

class SeedChargeableServices extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed/update Chargeable Services.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:seed-chargeable-services';

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
        \CircleLinkHealth\CcmBilling\Domain\Customer\SeedChargeableServices::execute();
    }
}
