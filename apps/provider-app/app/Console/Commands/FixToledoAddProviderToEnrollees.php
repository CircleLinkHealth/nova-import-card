<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;
use Illuminate\Console\Command;

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
    protected $signature = 'fix:toledo:add-provider-to-enrollees';

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
            ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT.'_2')
            ->with('user')
            ->inRandomOrder()
            ->chunkById(300, function ($enrollees) {
                foreach ($enrollees as $enrollee) {
                    $p = Demographics::where('practice_id', $enrollee->practice_id)
                        ->where('mrn', $enrollee->mrn)
                        ->firstOrFail();

                    $this->warn("Updating $enrollee->id");

                    if (empty($p->billing_provider_user_id) && ! empty($p->referring_provider_name)) {
                        $p->billing_provider_user_id = CcdaImporterWrapper::searchBillingProvider($p->referring_provider_name, $enrollee->practice_id);
                    }

                    if (empty($p->billing_provider_user_id)) {
                        $this->error("`$p->referring_provider_name` Billing Provider not found for $enrollee->id");
                        continue;
                    }

                    if ($p->isDirty()) {
                        $p->save();
                    }

                    $enrollee->provider_id = $p->billing_provider_user_id;
                    $enrollee->referring_provider_name = $p->referring_provider_name;
                    $enrollee->status = Enrollee::QUEUE_AUTO_ENROLLMENT;
                    $enrollee->save();

                    if ($enrollee->user) {
                        $enrollee->user->setBillingProviderId($enrollee->provider_id);
                    }
                }
            });
    }
}