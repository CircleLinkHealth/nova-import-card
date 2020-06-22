<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Imports\ToledoPracticeProviders\UpdateProvidersFromExcel;
use Illuminate\Console\Command;

class UpdateToledoProviderFromExcelCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uses data from excel to import data fro toledo providers';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:toledoProviders';

    /**
     * Create a new command instance.
     *
     * @return void
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
        \Excel::import(new UpdateProvidersFromExcel(), 'storage/toledo-provider-signatures/pcp_signature_file_for_clh.xlsx');
    }
}
