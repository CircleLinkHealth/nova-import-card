<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Search\ProviderByName;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class FillEnrolleesProviderId extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill provider ID on Enrollees';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:fillprovider {practiceId}';

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
        Enrollee::where('practice_id', $this->argument('practiceId'))->whereNull('provider_id')->whereNotNull('referring_provider_name')->chunkById(200, function ($enrollees) {
            $enrollees->each(function ($enrollee) {
                if ( ! empty($enrollee->provider_id)) {
                    return;
                }
                if ($provider = ProviderByName::first($enrollee->referring_provider_name)) {
                    $this->warn("Assign providerid:{$provider->id} to enrolleeid:{$enrollee->id}.");
                    $enrollee->provider_id = $provider->id;
                    $enrollee->save();
                }
            });
        });
    }
}
