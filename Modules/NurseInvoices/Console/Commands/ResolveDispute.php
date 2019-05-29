<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Notifications\ResolveDisputeReminder;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\Dispute;
use Illuminate\Console\Command;

class ResolveDispute extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve Dispute Reminder';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurseinvoice:resolveDispute';

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
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $disputes = Dispute::whereNull('resolved_at')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
        // @todo: should be Saras id here.

        $user = User::find(9521);

        if (0 !== $disputes) {
            $user->notify(new ResolveDisputeReminder($disputes));
        }

        //@todo:send invoices to accountant if there are no unresolved disputes
    }
}
