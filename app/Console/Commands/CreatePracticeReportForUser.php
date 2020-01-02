<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Contracts\Reports\PracticeDataExport;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

abstract class CreatePracticeReportForUser extends Command
{
    /**
     * @var PracticeDataExport
     */
    protected $report;

    public function getArguments()
    {
        return [
            ['practice_id', InputArgument::REQUIRED, 'The practice ID.'],
            ['user_id', InputArgument::REQUIRED, 'The user ID who will have access to download this report.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->report
            ->forPractice($this->argument('practice_id'))
            ->forUser($this->argument('user_id'))
            ->createMedia()
            ->notifyUser();

        $this->line('Command ran.');
    }
}
