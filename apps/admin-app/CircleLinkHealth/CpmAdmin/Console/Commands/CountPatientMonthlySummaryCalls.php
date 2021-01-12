<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\SharedModels\Repositories\CallRepository;
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
    protected $signature = 'count:calls {date? : the month we are counting for in format YYYY-MM-DD} {userIds? : comma separated. leave empty to check for all}';
    /**
     * @var \CircleLinkHealth\SharedModels\Repositories\CallRepository
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

    public function countCalls(Carbon $date, array $userIds)
    {
        PatientMonthlySummary::where('month_year', $date)
            ->when(
                ! empty($userIds),
                function ($q) use ($userIds) {
                    $q->whereIn('patient_id', $userIds);
                }
            )
            ->chunkById(500, function ($summaries) use ($date) {
                foreach ($summaries as $pms) {
                    \CircleLinkHealth\Customer\Jobs\CountPatientMonthlySummaryCalls::dispatch($pms->id, $date);
                }
            });
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

        if ( ! empty($userIds = $this->argument('userIds') ?? [])) {
            $userIds = explode(',', $userIds);
        }

        $this->countCalls($date, $userIds);

        $this->info('Jobs dispatched!');
    }
}
