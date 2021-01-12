<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Support\Facades\Log;
use MichaelLedin\LaravelJob\Job;

class RestoreEnrolleeProvidersFromRevisions extends Job implements ShouldBeEncrypted
{
    const MONTH_ALGOLIA_WAS_DISABLED = '2020-12-01';
    protected Carbon $date;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $date = null)
    {
        $this->date = $date ?? Carbon::parse(self::MONTH_ALGOLIA_WAS_DISABLED);
    }

    public static function fromParameters(string ...$parameters)
    {
        $date = isset($parameters[0]) ? Carbon::parse($parameters[0]) : null;

        return new static($date);
    }

    public function handle()
    {
        Log::channel('database')->info('Restoring providers for enrollees that mistakenly got their provider un-attached.');
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
                Log::channel('database')->info("Restoring provider for enrollee with ID: $enrollee->id.");
                $providerId = optional($enrollee->revisionHistory->where('key', 'provider_id')
                    ->whereNull('new_value')
                    ->whereNotNull('old_value')
                    ->sortByDesc('created_at')
                    ->first())->old_value;

                if ( ! User::ofType('provider')->where('id', $providerId)->exists()) {
                    Log::channel('database')->info("Error restoring provider for enrollee with ID: {$enrollee->id}. Provider with ID: {$providerId} not found");

                    return;
                }

                $enrollee->status = Enrollee::TO_CALL;
                $enrollee->provider_id = $providerId;
                $enrollee->save();
            });
    }
}
