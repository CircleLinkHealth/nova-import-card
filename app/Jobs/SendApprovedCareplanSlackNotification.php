<?php

namespace App\Jobs;

use App\CarePlan;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendApprovedCareplanSlackNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = Carbon::now();
        $careplans = CarePlan::with('providerApproverUser')
                             ->where('provider_date', '>=', $date->copy()->startOfDay())
                             ->get();

        $providers = [];
        foreach ($careplans as $careplan){
            if ($careplan->providerApproverUser) {
                $providers[] = $careplan->providerApproverUser->display_name;
            }
        }
        $doctors   = implode(',', $providers);

        sendSlackMessage('#careplanprintstatus',
            "{$careplans->count()} Care Plan(s) have been approved today by the following doctor(s): {$doctors}. \n
                    {$careplans->where('first_printed', null)->count()} Approved Care Plan(s) have not yet been printed.\n");
    }
}
