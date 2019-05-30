<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Notifications\InvoiceReminder;
use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\ValueObjects\NurseInvoiceDisputeDeadline;
use Illuminate\Console\Command;

class SendDisputeReminder extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder to review invoices';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseinvoices:dispute-reminder {month? : Month to generate the invoice for in YYYY-MM format. Defaults to previous month.} {userIds?* : Space separated. Leave empty to send to all}';

    /**
     * Create a new command instance.
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
        $month = $this->argument('month') ?? null;

        if ($month) {
            $month = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } else {
            $month = Carbon::now()->subMonth()->startOfMonth();
        }

        $userIds = (array) $this->argument('userIds') ?? [];

        $deadline = NurseInvoiceDisputeDeadline::forInvoiceOfMonth($month);

        NurseInvoice::with('nurse.user')
            ->when(
                ! empty($userIds),
                function ($q) use ($userIds) {
                    $q->whereHas(
                                'nurse.user',
                                function ($q) use ($userIds) {
                                    $q->whereIn('id', $userIds);
                                }
                            );
                }
                    )
            ->where('month_year', $month)
            ->undisputed()
            ->chunk(
                20,
                function ($invoices) use ($deadline, $month) {
                    foreach ($invoices as $invoice) {
                        $tz = $invoice->nurse->user->timezone ?? 'America/New_York';

                        $this->warn("Sending notification to {$invoice->nurse->user->getFullName()}");
                        $invoice->nurse->user->notify(new InvoiceReminder($deadline->copy()->setTimezone($tz), $month));
                        $this->info("Sent notification to {$invoice->nurse->user->getFullName()}");
                    }
                }
                    );

        $this->info('Command finished!');
    }
}
