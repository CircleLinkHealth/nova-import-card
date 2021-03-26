<?php

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UpdateCorrectProvidersToEnrollees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:enrollee-providers {practiceId} {enrolleeIdsWithProviderIds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Update the provider id of given enrolleeIds paired with WithProviderIds. {enrolleeIdsWithProviderIds} should be json_encoded
    and passed to argument using single quotes: '{'enrolleeId1':'providerId_x', 'enrolleeId2':'providerId_y'}'.";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function decodedInputDataToUpdate():\stdClass
    {
        return json_decode($this->argument('enrolleeIdsWithProviderIds'));
    }

    public function getEnrolleesToUpdateGrouppedByProviders():Collection
    {
        return collect($this->decodedInputDataToUpdate())
            ->mapToGroups(function ($providerId, $enrolleeId){
                return [
                    $providerId => $enrolleeId
                ];
            });
    }

    /**
     *
     */
    public function handle()
    {
        $enrolleesToUpdateGrouppedByProvider =  $this->getEnrolleesToUpdateGrouppedByProviders();

        if ($enrolleesToUpdateGrouppedByProvider->isEmpty()){
            $this->info("Failed to parse enrolleeIdsWithProviderIds input data. Try: passing encoded data in single quotes.");
            return;
        }

        $enrollees = Enrollee::where('practice_id', $this->argument('practiceId'));

        $enrolleesToUpdateGrouppedByProvider->each(function ($enrolleeIds,$providerId) use($enrollees) {
            $this->info("Updating enrollees for Provider $providerId");
            $updated = $enrollees->whereIn('id', $enrolleeIds->toArray())
                ->update(
                    [
                        'provider_id' => $providerId
                    ]);

            $count = $enrolleeIds->count();
            $this->info("Attempted to update $count Enrollees for Provider $providerId and ended with status: $updated");
        });
    }
}