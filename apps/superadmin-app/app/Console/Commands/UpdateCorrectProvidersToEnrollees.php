<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UpdateCorrectProvidersToEnrollees extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Update the referring_provider_name of given enrolleeIds paired with WithProviderNames. {enrolleeIdsWithProviderNames} should be json_encoded
    and passed to argument using single quotes: '{'enrolleeId1':'provider_x', 'enrolleeId2':'provider_y'}'.";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:enrollee-providers {practiceId} {enrolleeIdsWithProviderNames}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function decodedInputDataToUpdate(): \stdClass
    {
        return json_decode($this->argument('enrolleeIdsWithProviderNames'));
    }

    public function getEnrolleesToUpdateGrouppedByProviders(): Collection
    {
        return collect($this->decodedInputDataToUpdate())
            ->mapToGroups(function ($providerName, $enrolleeId) {
                return [
                    $providerName => $enrolleeId,
                ];
            });
    }

    public function handle()
    {
        $enrolleesToUpdateGrouppedByProvider = $this->getEnrolleesToUpdateGrouppedByProviders();

        if ($enrolleesToUpdateGrouppedByProvider->isEmpty()) {
            $this->info('Failed to parse enrolleeIdsWithProviderNames input data. Try: passing encoded data in single quotes.');

            return;
        }

        $enrolleesToUpdateGrouppedByProvider->each(function ($enrolleeIds, $providerName) {
            $this->info("Updating enrollees for Provider $providerName");
            $updated = Enrollee::where('practice_id', $this->argument('practiceId'))
                ->whereNull('provider_id')
                ->whereIn('id', $enrolleeIds->toArray())
                ->update(
                    [
                        'referring_provider_name' => $providerName,
                    ]
                );

            $count = $enrolleeIds->count();
            $this->info("Attempted to update $count Enrollees for Provider $providerName and ended with status: $updated");
        });
    }
}
