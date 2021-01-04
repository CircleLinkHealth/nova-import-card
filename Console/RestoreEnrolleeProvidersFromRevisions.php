<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class RestoreEnrolleeProvidersFromRevisions extends Command
{
    const MONTH_ALGOLIA_WAS_DISABLED = '2020-12-01';
    protected Carbon $date;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore Providers from revisions. (When providers were wiped due to bug).';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'enrollees:restore-providers';

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
     * @return mixed
     */
    public function handle()
    {
        $this->date = $date ?? Carbon::parse(self::MONTH_ALGOLIA_WAS_DISABLED);

        $this->info('Restoring providers for enrollees that mistakenly got their provider un-attached.');
        Enrollee::with(['revisionHistory'])
            ->whereNull('provider_id')
            ->where('status', Enrollee::INELIGIBLE)
            ->whereHas('revisionHistory', function ($r) {
                $r->where('created_at', '>=', $this->date)
                    ->where('key', 'provider_id')
                    ->whereNull('new_value')
                    ->whereNotNull('old_value');
            })
            ->each(function (Enrollee $enrollee) {
                $this->info("Restoring provider for enrollee with ID: $enrollee->id.");
                $providerId = optional($enrollee->revisionHistory->where('key', 'provider_id')
                    ->whereNull('new_value')
                    ->whereNotNull('old_value')
                    ->sortByDesc('created_at')
                    ->first())->old_value;

                if ( ! User::ofType(['provider', 'office_admin'])->where('id', $providerId)->exists()) {
                    $this->info("Error restoring provider for enrollee with ID: {$enrollee->id}. Provider with ID: {$providerId} not found");

                    return;
                }

                $enrollee->status = Enrollee::TO_CALL;
                $enrollee->provider_id = $providerId;
                $enrollee->save();
            });
    }
}
