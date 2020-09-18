<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Filters\CallViewFilters;
use CircleLinkHealth\CpmAdmin\Http\Controllers\Reports\CallReportController;
use App\Notifications\PatientActivityManagementEndOfMonthReportGeneratedNotification;
use App\User;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GenerateReportForScheduledPAM extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an excel report for scheduled activities in PAM';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:pam';

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
     * @return void
     */
    public function handle()
    {
        $user = $this->getOpsAdminUser();
        if ( ! $user) {
            $msg = 'Could not find ops admin user. Make sure config is set.';
            Log::error($msg);

            return;
        }

        auth()->loginUsingId($user->id);

        /** @var CallReportController $controller */
        $controller = app(CallReportController::class);

        $date    = Carbon::now()->startOfMonth();
        $request = new Request();
        $request->merge([
            'scheduled' => true,
        ]);
        $filters = new CallViewFilters($request);
        $mediaId = $controller->generateXlsAndSaveToMedia($date, $filters);

        $user->notify(new PatientActivityManagementEndOfMonthReportGeneratedNotification($mediaId, $date));
        $this->info("Done. User[$user->id] should have been notified.");
    }

    private function getOpsAdminUser()
    {
        $opsAdminId = AppConfig::pull('ops_admin_user_id', null);
        if ($opsAdminId) {
            return User::findOrFail($opsAdminId);
        }

        if (app()->environment(['staging', 'review'])) {
            return User::whereUsername('admin')->first();
        }

        return User::whereEmail('ethan@circlelinkhealth.com')->first();
    }
}
