<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\CareAmbassadorLog;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCareAmbassadorLogs implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $careAmbassadorUser;
    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $careAmbassadorUser, Carbon $date)
    {
        $this->careAmbassadorUser = $careAmbassadorUser;
        $this->date               = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $careAmbassadorModel = $this->careAmbassadorUser->careAmbassador;

        if ( ! $careAmbassadorModel) {
            Log::critical("Care Ambassador model not found for User with ID: {$this->careAmbassadorUser->id}");
        }

        $log = CareAmbassadorLog::createOrGetLogs($careAmbassadorModel->id, $this->date);

        //add stats
    }
}
