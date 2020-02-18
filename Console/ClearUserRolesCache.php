<?php

namespace CircleLinkHealth\Customer\Console;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Tasks\ClearUserCache;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearUserRolesCache extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'user-cache-clear:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the roles cache of a user.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ClearUserCache::roles(User::findOrFail($this->argument('userId')));
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
