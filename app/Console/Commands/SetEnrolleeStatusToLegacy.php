<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class SetEnrolleeStatusToLegacy extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets existing enrollee status to legacy, to prepare database with Care Ambassador Panel Integration';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollee:setStatusToLegacy';

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
        Enrollee::where('status', '!=', Enrollee::ENROLLED)
            ->update([
                'status' => Enrollee::LEGACY,
            ]);
    }
}
