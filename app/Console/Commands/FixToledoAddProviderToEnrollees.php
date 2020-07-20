<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Models\PracticePull\Demographics;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;
use Modules\Eligibility\CcdaImporter\CcdaImporterWrapper;

class FixToledoAddProviderToEnrollees extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add provider to Toledo Enrollees';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:toledo:add-provider-to-enrollees { minimumId? } { --participants-only }';

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
        Enrollee::where('practice_id', 235)
            ->with('user.patientInfo')
            ->whereHas('user', function ($q) {
                $q->ofType('participant')->has('patientInfo');
            })
            ->when($this->option('participants-only'), function ($q) {
                $q->where('id', '>=', (int) $this->argument('minimumId'));
            })
            ->when(is_numeric($this->argument('minimumId')), function ($q) {
                $q->where('id', '>=', (int) $this->argument('minimumId'));
            })
            ->chunkById(300, function ($enrollees) {
                foreach ($enrollees as $enrollee) {
                    $p = Demographics::where('practice_id', $enrollee->practice_id)
                        ->where('mrn', $enrollee->mrn)
                        ->firstOrFail();

                    $this->warn("Updating $enrollee->id");

                    if (empty($p->billing_provider_user_id)) {
                        $p->billing_provider_user_id = optional(CcdaImporterWrapper::searchBillingProvider($p->referring_provider_name, $enrollee->practice_id))->id;
                    }

                    if (empty($p->billing_provider_user_id)) {
                        $this->error("`$p->referring_provider_name` Billing Provider not found for $enrollee->id");
                        continue;
                    }

                    $enrollee->provider_id = $p->billing_provider_user_id;
                    $enrollee->referring_provider_name = $p->referring_provider_name;

                    if ($enrollee->user) {
                        $enrollee->user->setBillingProviderId($enrollee->provider_id);

                        $location = CcdaImporterWrapper::searchLocation($p->facility_name, $enrollee->practice_id);
                        $this->setLocation($enrollee->user, $location);
                        $p->location_id = $location->id;
                        $enrollee->location_id = $location->id;
                    }

                    if ($p->isDirty()) {
                        $p->save();
                    }

                    if ($enrollee->isDirty()) {
                        $enrollee->save();
                    }
                }
            });
    }

    private function setLocation(User $user, Location $location)
    {
        $user->setPreferredContactLocation($location->id);
        $user->locations()->sync([$location->id]);
    }
}
