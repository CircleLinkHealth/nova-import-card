<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Constants;
use CircleLinkHealth\Core\Traits\RunsConsoleCommands;
use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Migrations\Migrator;

class MigrateWithComposerIde extends MigrateCommand
{
    use RunsConsoleCommands;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct($migrator);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $this->runCpmCommand(['composer', 'ide'], true, Constants::TEN_MINUTES_IN_SECONDS);
    }
}
