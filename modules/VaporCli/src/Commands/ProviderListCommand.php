<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;

class ProviderListCommand extends Command
{
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->table([
            'ID', 'Name',
        ], collect($this->vapor->providers())->map(function ($provider) {
            return [
                $provider['id'],
                $provider['name'],
            ];
        })->all());
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('provider:list')
            ->setDescription('List the cloud provider accounts linked to the current team');
    }
}
