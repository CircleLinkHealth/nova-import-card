<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Contracts\Reports\PracticeDataExportInterface;
use App\Jobs\StoreReportAsMedia;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class CreatePracticeReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a practice report class.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'reports:create';

    /**
     * @var PracticeDataExportInterface
     */
    private $report;

    public function handle()
    {
        $report = $this->getReportClass();

        $report->forPractice($this->argument('practice_id'))
            ->forUser($this->argument('user_id'))
            ->queue($report->filename(), $report::STORE_TEMP_REPORT_ON_DISK, \Maatwebsite\Excel\Excel::XLS)
            ->chain(
                [
                    new StoreReportAsMedia($report->filename(), $report::STORE_TEMP_REPORT_ON_DISK, $this->argument('practice_id'), $report->mediaCollectionName(), $this->argument('user_id')),
                ]
            );

        $this->info('Report command ran.');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['class', InputArgument::REQUIRED, 'The FQN class name of the report to run'],
            ['practice_id', InputArgument::REQUIRED, 'The practice ID.'],
            ['user_id', InputArgument::REQUIRED, 'The user ID who will have access to download this report.'],
        ];
    }

    /**
     * Get a report class instance from the container.
     */
    protected function getReportClass(): PracticeDataExportInterface
    {
        if ( ! $this->report) {
            $this->report = $this->laravel->make($this->input->getArgument('class'));
        }

        return $this->report;
    }
}
