<?php

namespace App\Console\Commands;

use App\EligibilityJob;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Console\Command;

class CreateEnrolleesFromEligbleJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:enrolleesFromEligibleJobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create enrollees from eligibility jobs where `outcome = eligible` who do not have enrollees created.';

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
     * @return mixed
     */
    public function handle()
    {
        $counter = 0;

        EligibilityJob::eligible()
                      ->doesntHave('enrollee')
                      ->with('batch.practice')
                      ->take(5)
                      ->get()
                      ->each(function ($job) use (&$counter) {
                          $generator = new WelcomeCallListGenerator(
                              collect([$job->data]),
                              (boolean)$job->batch->options['filterLastEncounter'],
                              (boolean)$job->batch->options['filterInsurance'],
                              (boolean)$job->batch->options['filterProblems'],
                              true,
                              $job->batch->practice,
                              null,
                              null,
                              $job->batch,
                              $job
                          );

                          $generator->createEnrollees();

                          $counter++;
                      });

        $this->info("$counter jobs scheduled.");
    }
}
