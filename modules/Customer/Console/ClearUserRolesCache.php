<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Console;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ClearUserRolesCache extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the roles cache of a user.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'user-cache-clear:roles';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::findOrFail($this->argument('userId'));
        $user->clearRolesCache();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['userId', InputArgument::REQUIRED, 'The user ID whose cache we are clearing.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
