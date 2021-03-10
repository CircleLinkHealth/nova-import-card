<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;

class ProjectDeleteCommand extends Command
{
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! Helpers::confirm('Are you sure you want to delete this project', false)) {
            Helpers::abort('Action cancelled.');
        }

        $this->vapor->deleteProject(Manifest::id());

        @unlink(Path::current().'/vapor.yml');

        Helpers::info('Project deletion initiated successfully.');
        Helpers::line();
        Helpers::line('The project deletion process may take several minutes to complete.');
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('project:delete')
            ->setDescription('Delete the project');
    }
}
