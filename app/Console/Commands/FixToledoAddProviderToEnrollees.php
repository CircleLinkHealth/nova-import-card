<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Models\PracticePull\Demographics;
use App\Search\ProviderByName;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
        Demographics::whereIn('mrn', function ($q) {
            $q->select('mrn')
                ->from('enrollees')
                ->where('practice_id', 235)
                ->whereStatus(Enrollee::QUEUE_AUTO_ENROLLMENT.'_2');
        })->chunkById(500, function ($q) {
            foreach ($q as $p) {
                $this->warn("Updating $p->mrn");
                $p->billing_provider_user_id = optional(ProviderByName::first($p->referring_provider_name))->id;

                if (empty($p->billing_provider_user_id)) {
                    continue;
                }

                $p->save();

                Enrollee::wherePracticeId(235)->whereMrn($p->mrn)->update([
                    'provider_id' => $p->billing_provider_user_id,
                    'status'      => Enrollee::QUEUE_AUTO_ENROLLMENT,
                ]);

                $enrollee = Enrollee::wherePracticeId(235)->whereMrn($p->mrn)->with('user')->has('user')->first();

                if ($enrollee) {
                    $enrollee->user->setBillingProviderId($enrollee->provider_id);
                }
            }
        });
    }
}
