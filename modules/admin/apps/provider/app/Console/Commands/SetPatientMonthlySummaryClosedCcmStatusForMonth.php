<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class SetPatientMonthlySummaryClosedCcmStatusForMonth extends Command
{
    use DryRunnable;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the CCM status of the patient for the given month.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'pms:setClosedMonthStatus';
    /**
     * @var int
     */
    private $changedCount = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $argument = $this->argument('month') ?? null;

        $date = $argument
            ? Carbon::parse($argument)->startOfMonth()
            : Carbon::now()->subMonth()->startOfMonth();

        PatientMonthlySummary::orderBy('id')
            ->whereMonthYear($date->toDateString())
            ->with('patient.patientInfo')
            ->has('patient.patientInfo')
            ->chunk(
                500,
                function ($summaries) use ($date) {
                    $summaries->each(
                        function (PatientMonthlySummary $summary) use ($date) {
                            $actualStatus = $summary->patient->patientInfo->getCcmStatusForMonth(
                                $date
                            );
                            if ($summary->closed_ccm_status !== $actualStatus) {
                                $this->warn(
                                    "changing patient:{$summary->patient->id} summary:$summary->id"
                                );

                                if ( ! $this->isDryRun()) {
                                    $summary->closed_ccm_status = $actualStatus;
                                    $summary->save();
                                    ++$this->changedCount;
                                }
                            }
                        }
                    );
                }
            );
        $this->info("{$this->changedCount} patient summaries changed.");
    }

    protected function getArguments()
    {
        return [
            [
                'month',
                InputArgument::REQUIRED,
                'Month to set statuses for in "Y-m-d": format. Defaults to previous month.',
            ],
        ];
    }
}
