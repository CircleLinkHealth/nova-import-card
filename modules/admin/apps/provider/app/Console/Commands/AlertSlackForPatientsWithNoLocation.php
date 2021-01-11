<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Console\Command;

class AlertSlackForPatientsWithNoLocation extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert Slack regarding patients with no preferred contact location.';
    //todo: maybe move to billing module afterwards

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:check';

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
     * @return int
     */
    public function handle()
    {
        $patientsWithNoLocation = User::whereHas('patientInfo', fn ($pi) => $pi->enrolled()->whereNull('preferred_contact_location'))
            ->whereHas('primaryPractice', fn ($p) => $p->where('is_demo', false))
            ->whereHas('carePlan', fn ($cp) => $cp->whereIn('status', [
                CarePlan::QA_APPROVED,
                CarePlan::RN_APPROVED,
                CarePlan::PROVIDER_APPROVED,
            ]))
            ->pluck('id')
            ->toArray();

        if (empty($patientsWithNoLocation)) {
            sendSlackMessage('#billing_alerts', 'Success!!  We found no patients without locations. Rejoice!');

            return;
        }

        $ids = implode(',', $patientsWithNoLocation);
        sendSlackMessage('#billing_alerts', "(From check Patient Location Command:) Warning! The following patients do not have a preferred contact location: {$ids}");
    }
}
