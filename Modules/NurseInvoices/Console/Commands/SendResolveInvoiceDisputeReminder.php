<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\Jobs\GenerateNurseMonthlyInvoiceCsv;
use App\Notifications\ResolveDisputeReminder;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\Dispute;
use Illuminate\Console\Command;

class SendResolveInvoiceDisputeReminder extends Command
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
    protected $signature = 'nurseinvoice:resolveDispute {month? : Invoices for month for in YYYY-MM format. Defaults to previous month.}';

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

        $disputes = Dispute::whereNull('resolved_at')
            ->where('month_year', $month)
            ->count();

        if (0 !== $disputes && isProductionEnv()) {
            $sara = User::whereEmail('sheller@circlelinkhealth.com')->first();

            if ($sara) {
                $sara->notify(new ResolveDisputeReminder($disputes));
            }

            //send invoices csv to accountant
            //@todo:should i move this to a controller or sercice class?
            GenerateNurseMonthlyInvoiceCsv::dispatch($month)
                ->onQueue('high');
        }
    }
}
