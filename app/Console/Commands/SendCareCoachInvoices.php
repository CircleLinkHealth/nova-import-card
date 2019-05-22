<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\CreateNurseInvoices;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class SendCareCoachInvoices extends Command
{
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
    protected $signature = 'nurses:send-invoices {--variable-time : Use Variable Time pay algorithm.} {month? : Month to send the invoice for in YYYY-MM format. Defaults to previous month.} {userIds?* : Space separated. Leave empty to send to all}';

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
            $month = Carbon::createFromFormat('Y-m', $month);
        } else {
            $month = Carbon::now()->subMonths(2);
        }

        $start = $month->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $userIds = (array) $this->argument('userIds') ?? null;

        $users = User::ofType('care-center')
            ->whereHas(
                'pageTimersAsProvider',
                function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start, $end]);
                }
                     )->when(
                         ! empty($userIds),
                         function ($q) use ($userIds) {
                             $q->whereIn('id', $userIds);
                         }
            )->get();

        if ($users->isEmpty()) {
            $this->info('Could not find Users with page activity within the given dates.');

            return;
        }

        $this->info("Sending invoices to Nurses below for {$start->englishMonth}, {$start->year}");

        $this->table(
            ['name', 'id'],
            $users->map(
                function ($u) {
                    return [$u->getFullName(), $u->id];
                }
            )
        );

        CreateNurseInvoices::dispatch(
            $start,
            $end,
            $users->pluck('id')->all()
        );

        $this->info('All done!');
    }
}
