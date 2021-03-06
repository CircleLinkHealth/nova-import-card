<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SecretDeleteCommand extends Command
{
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->vapor->deleteSecret($this->getSecretId($this->vapor->secrets(
            Manifest::id(),
            $this->argument('environment')
        )));

        Helpers::info('Secret deleted successfully.');
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('secret:delete')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name', 'staging')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The secret name')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Perform the action without confirmation')
            ->setDescription('Delete a secret from an environment');
    }

    /**
     * Get the ID for the secret that should be deleted.
     *
     *
     * @return string
     */
    protected function getSecretId(array $secrets)
    {
        if (empty($secrets)) {
            Helpers::abort('This environment does not have any secrets.');
        }

        if ($this->option('name')) {
            return $this->getSecretIdByName($secrets, $this->option('name'));
        }

        return $this->menu(
            'Which secret would you like to delete?',
            collect($secrets)->mapWithKeys(function ($secret) {
                return [$secret['id'] => $secret['name']];
            })->all()
        );
    }

    /**
     * Get the ID of a secret by name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getSecretIdByName(array $secrets, $name)
    {
        $id = collect($secrets)->where('name', $name)->first()['id'] ?? null;

        if (is_null($id)) {
            Helpers::abort('Unable to find a secret with that name.');
        }

        return $id;
    }
}
