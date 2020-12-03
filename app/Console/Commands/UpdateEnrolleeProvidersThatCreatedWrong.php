<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\UpdateEnrolleeFromCollectionJob;
use App\ValueObjects\SelfEnrolment\MarillacEnrolleeProvidersValueObject;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Console\Command;

class UpdateEnrolleeProvidersThatCreatedWrong extends Command
{
    const MARILLAC_NAME = 'marillac-clinic-inc';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Enrollees that got assign the wrong Provider.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:enrollee-providers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $practice = Practice::where('name', self::MARILLAC_NAME)->firstOrFail();

        $dataToUpdate = (new MarillacEnrolleeProvidersValueObject())->dataGroupedByProvider();
        UpdateEnrolleeFromCollectionJob::dispatch($dataToUpdate, $practice->id);
    }
}
