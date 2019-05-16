<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\SaveInvoicableNursesService;
use Illuminate\Console\Command;

class CollectInvoicableNurses extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save Nurses Nova Invoices table';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:collectInvoicableNurses';
    private $service;

    /**
     * Create a new command instance.
     *
     * @param SaveInvoicableNursesService $service
     */
    public function __construct(SaveInvoicableNursesService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Returns all nurses selected for time period in admin/reports/nurse/invoice.
     */
    public function handle()
    {
        $this->service->getInvoicableNurses();
        //@todo: Should notify somenone on slack or mail
    }
}
