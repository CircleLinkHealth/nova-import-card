<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\VaporDevOpsHelpers\Commands;

use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;

class DeleteAllSecrets extends Command
{
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        collect($this->vapor->secrets(
            Manifest::id(),
            $this->argument('environment')
        ))->each(function (array $secret) {
            $this->vapor->deleteSecret($secret['id']);

            Helpers::info("Secret[{$secret['name']}] deleted successfully.");
        });

        Helpers::info('Command finished.');
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cpmvapor:clearsecrets')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->setDescription('Delete all the secrets for a given environment');
    }
}
