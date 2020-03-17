<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Repositories\CallRepository;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Console\Command;

class CountPatientMonthlySummaryCalls extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate no_of_calls and no_of_successful_calls on patient monthly summary for a given month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count:calls {date? : the month we are counting for in format YYYY-MM-DD}';
    /**
     * @var CallRepository
     */
    private $callRepository;

    /**
     * The counter of records that were changed.
     *
     * @var int
     */
    private $changedCount = 0;

    /**
     * Create a new command instance.
     */
    public function __construct(CallRepository $callRepository)
    {
        parent::__construct();
        $this->callRepository = $callRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $argument = $this->argument('date') ?? null;

        $date = $argument
            ? Carbon::parse($argument)->startOfMonth()
            : Carbon::now()->startOfMonth();

        PatientMonthlySummary::orderBy('id')
            ->whereMonthYear($date->toDateString())
            ->chunk(500, function ($summaries) use ($date) {
                foreach ($summaries as $pms) {
                    $save = false;

                    $noOfSuccessfulCalls = $this->callRepository->numberOfSuccessfulCalls(
                        $pms->patient_id,
                        $date
                    );

                    if ($noOfSuccessfulCalls != $pms->no_of_successful_calls) {
                        $this->comment("user_id:{$pms->patient_id} no_of_successful_calls changing from {$pms->no_of_successful_calls} to ${noOfSuccessfulCalls}");
                        $pms->no_of_successful_calls = $noOfSuccessfulCalls;
                        $save = true;
                    }

                    $noOfCalls = $this->callRepository->numberOfCalls($pms->patient_id, $date);

                    if ($noOfCalls != $pms->no_of_calls) {
                        $this->comment("user_id:{$pms->patient_id} no_of_calls changing from {$pms->no_of_calls} to ${noOfCalls}");
                        $pms->no_of_calls = $noOfCalls;
                        $save = true;
                    }

                    if ($save) {
                        $pms->save();
                        $this->incrementCounter();
                    }
                }
            });

        $this->info('Calls were counted successfully!');
        $this->info("{$this->changedCount} patient summaries changed.");
    }

    private function incrementCounter()
    {
        ++$this->changedCount;
    }
}
