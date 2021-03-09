<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Nova\Actions\ModifyPatientTime;
use Illuminate\Console\Command;

class ModifyPatientTimeCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Modify patient\'s time for current month';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modify:patient-time {userId} {csCode} {newTimeSeconds} {allowLessThan20Minutes?}';

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
     * @return int
     */
    public function handle()
    {
        $patientId              = $this->argument('userId');
        $csCode                 = $this->argument('csCode');
        $newTime                = $this->argument('newTimeSeconds');
        $allowLessThan20Minutes = $this->argument('allowLessThan20Minutes') ?? false;

        (new ModifyPatientTime($patientId, $csCode, $newTime, $allowLessThan20Minutes))->execute();

        return 0;
    }
}
