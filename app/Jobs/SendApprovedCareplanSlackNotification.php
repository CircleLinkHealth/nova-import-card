<?php

namespace App\Jobs;

use App\CarePlan;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
     * @return bool|void
     */
    public function handle()
    {
        $date = Carbon::now();

        $careplans = CarePlan::with('providerApproverUser')
                             ->where([
                                 ['provider_date', '>=', $date->copy()->startOfDay()],
                                 ['provider_date', '<=', $date->copy()->endOfDay()],
                             ])
                             ->get();

        if ($careplans->isEmpty()) {
            if (app()->environment(['worker', 'production'])) {
                sendSlackMessage('#careplanprintstatus', "0 Care Plan(s) have been approved today.");
            }
        } else {
            $providers = $careplans->groupBy('providerApproverUser.display_name')
                                   ->map(function ($careplansCol, $providerName) {
                                       return "$providerName: {$careplansCol->count()} careplans";
                                   });

            $message = "{$careplans->count()} Care Plan(s) have been approved today by the following doctor(s): {$providers->implode(', ')}. 
                    \n {$careplans->where('first_printed', null)->count()} Approved Care Plan(s) have not yet been printed.";

            if (app()->environment('staging')) {
                $message = "(This is a test from staging) $message";
            }

            sendSlackMessage('#careplanprintstatus', $message);
        }
    }
}
