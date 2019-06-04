<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Console\Commands;

use App\AppConfig;
use App\Jobs\GenerateNurseMonthlyInvoiceCsv;
use App\Notifications\ResolveDisputeReminder;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendResolveInvoiceDisputeReminder extends Command
{
    const NURSE_DISPUTES_MANAGER = 'nurse_invoice_dispute_manager';

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

        $this->info('Run command for month: '.$month->toDateString());

        $disputesCount = NurseInvoice::where('month_year', $month)
            ->whereHas('dispute', function ($q) use ($month) {
                $q->whereNull('resolved_at');
            })
            ->count();

        if (0 !== $disputesCount && isProductionEnv()) {
            $usersToSendEmail = $this->usersToSendEmail();

            Notification::send($usersToSendEmail, new ResolveDisputeReminder($disputesCount));
        }
        //@todo:should i move this to a controller or sercice class?
        GenerateNurseMonthlyInvoiceCsv::dispatch($month)
            ->onQueue('high');

        $this->info('Command finished');
    }

    public function usersToSendEmail()
    {
        $getEmails = [];

        AppConfig::where('config_key', '=', self::NURSE_DISPUTES_MANAGER)
            ->select('config_value')->chunk(20, function ($emails) use (&$getEmails) {
                foreach ($emails as $email) {
                    $getEmails[] = $email->config_value;
                    $this->info('Will email'.$email->config_value);
                }
            });

        return User::whereIn('email', $getEmails)->get();
    }
}
