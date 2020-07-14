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
            throw new \Exception('You cannot execute this action in production environment');
        }

        if (is_null($practiceName)) {
            throw new \Exception('Practice input is required');
        }

        (new \PrepareDataForReEnrollmentTestSeeder($practiceName))->run();
    }
}
