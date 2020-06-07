<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Tests;

use CircleLinkHealth\NurseInvoices\Console\Commands\SendMonthlyNurseInvoiceLAN;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendResolveInvoiceDisputeReminder;
use Tests\CustomerTestCase;

class ImportantCommandsRunTest extends CustomerTestCase
{
    public function test_commands_run()
    {
        foreach ($this->commands() as $name => $args) {
            if (is_array($args)) {
                $this->artisan($name, $args)->assertExitCode(0);
                continue;
            }

            $this->artisan($args)->assertExitCode(0);
        }
    }

    private function commands()
    {
        return [
            SendResolveInvoiceDisputeReminder::class,
            SendResolveInvoiceDisputeReminder::class => [
                now()->subMonth()->startOfMonth()->toDateString(),
            ],
            SendMonthlyNurseInvoiceLAN::class,
            SendMonthlyNurseInvoiceLAN::class => [
                now()->subMonth()->startOfMonth()->toDateString(),
            ],
        ];
    }
}
