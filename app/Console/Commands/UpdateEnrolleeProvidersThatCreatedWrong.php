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
    protected $description = 'Updates Enrollees that got assign with wrong Provider. Also updates enrollee-status depending on pending letter/enrolment status!';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:marillac-enrollee';

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
