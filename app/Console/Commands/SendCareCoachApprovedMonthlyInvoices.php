<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Notifications\InvoiceBeforePayment;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\NurseInvoices\Traits\TakesMonthAndUsersAsInputArguments;
use Illuminate\Console\Command;

class SendCareCoachApprovedMonthlyInvoices extends Command
{
    use DryRunnable {
        getOptions as traitGetOptions;
    }
    use TakesMonthAndUsersAsInputArguments {
        getArguments as traitGetArgs;
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and send invoices to Care Coaches';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'nurses:send-invoices';

    /**
     * The default month, if no argument is passed.
     *
     * @return Carbon
     */
    public function defaultMonth()
    {
        return Carbon::now()->subMonths(2)->startOfMonth();
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
        $this->info('Running command for '.$this->month()->toDateString());

        $sent = $this->createAndSendInvoices();

        if ($sent->isNotEmpty()) {
            $this->table(
                ['name', 'id'],
                $sent->map(
                    function ($u) {
                        return [$u->getFullName(), $u->id];
                    }
                )
            );
        }

        $this->info('All done!');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Support\Collection
     */
    private function createAndSendInvoices()
    {
        $sent = collect();

        $this->invoices()->chunk(20, function ($invoices) use ($sent) {
            foreach ($invoices as $invoice) {
                /** @var NurseInvoice $invoice */

                /** @var User $user */
                $user = $invoice->nurseInfo->user;

                $this->warn("Preparing and sending for: {$user->getFullNameWithId()}");

                if ( ! $this->isDryRun()) {
                    $media = $invoice->toPdfAndStoreAsMedia();
                    $user->notify(new InvoiceBeforePayment($invoice, $media));
                    $sent->push($user);
                }

                $this->warn("Sent invoice to: {$invoice->nurseInfo->user->getFullNameWithId()}");
            }
        });

        return $sent;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return mixed
     */
    private function invoices()
    {
        return NurseInvoice::where('month_year', $this->month())->when(
            ! empty($this->usersIds()),
            function ($q) {
                $q->ofNurses($this->usersIds());
            }
        )->whereNotNull('sent_to_accountant_at')
            ->whereHas(
                'nurseInfo.user',
                function ($q) {
                    $q->ofType('care-center');
                }
            )->with(['nurseInfo.user' => function ($q) {
                $q->withTrashed();
            }]);
    }
}
