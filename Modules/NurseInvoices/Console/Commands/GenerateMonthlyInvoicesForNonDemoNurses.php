<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Jobs\CreateNurseInvoices;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\NurseInvoices\Traits\TakesMonthAndUsersAsInputArguments;
use Illuminate\Console\Command;

class GenerateMonthlyInvoicesForNonDemoNurses extends Command
{
    use DryRunnable;
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

        if ($this->month()->isCurrentMonth(true)) {
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
            $requestedBy = null,
            true
        );

        $this->info('Command dispatched!');

        $forNurses = empty($userIds)
            ? 'all nurses.'
            : 'nurses with user IDs '.implode(', ', $userIds);

        $this->info(
            "Will create invoices for {$this->month()->format('Y-m')}, for $forNurses."
        );
    }
}
