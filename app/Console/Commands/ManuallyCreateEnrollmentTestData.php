<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ManuallyCreateEnrollmentTestData extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Triggers 'PrepareDataForReEnrollmentTestSeeder'.
    Accepts practice name as parameter like: 'mario-bros-clinic'";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:selfEnrollmentTestData {practiceName}';

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
     * @throws \Exception
     *
     * @return int
     */
    public function handle()
    {
        $practiceName = $this->argument('practiceName') ?? null;

        if (isProductionEnv()) {
            $this->warn('You cannot execute this action in production environment');

            return;
        }

        if (is_null($practiceName)) {
            $this->warn('Practice input is required');

            return;
        }

        (new \PrepareDataForReEnrollmentTestSeeder($practiceName))->run();
    }
}
