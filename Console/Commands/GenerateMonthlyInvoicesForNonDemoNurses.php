<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Jobs\CreateNurseInvoices;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\NurseInvoices\Traits\TakesMonthAndUsersAsInputArguments;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class GenerateMonthlyInvoicesForNonDemoNurses extends Command
{
    use DryRunnable {
        getOptions as dryGetOptions;
    }
    use TakesMonthAndUsersAsInputArguments;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for nurses who worked last month.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'nurseinvoices:create';

    public function defaultMonth()
    {
        return now()->startOfMonth();
    }

    /**
     * Execute the console command.
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return mixed
     */
    public function handle()
    {
        $start = $this->month()->startOfMonth();

        if ($this->option('allow-same-day')) {
            $end = now()->endOfDay();
        }
        //if it's the first of the month, process the the last day of the previous month
        elseif (1 === now()->day && $start->isCurrentMonth(true)) {
            $start = now()->subMonth()->startOfMonth();
            $end   = $start->copy()->endOfMonth();
        } elseif ($this->month()->isCurrentMonth(true)) {
            $end = now()->subDay()->endOfDay();
        } else {
            $end = $this->month()->endOfMonth();
        }

        $userIds = $this->usersIds();

        CreateNurseInvoices::dispatch(
            $start,
            $end,
            $userIds,
            false,
            null,
            true
        );

        $this->info('Command dispatched!');

        $forNurses = empty($userIds)
            ? 'all nurses.'
            : 'nurses with user IDs '.implode(', ', $userIds);

        $this->info(
            "Will create invoices for {$start->format('Y-m')}, for $forNurses."
        );
    }

    protected function getOptions()
    {
        $arr = $this->dryGetOptions();

        return array_merge($arr, [
            ['allow-same-day', 'a', InputOption::VALUE_NONE, 'Allow same day invoice.', null],
        ]);
    }
}
