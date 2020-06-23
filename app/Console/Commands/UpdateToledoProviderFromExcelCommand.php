<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Imports\ToledoPracticeProviders\UpdateProvidersFromExcel;
use CircleLinkHealth\Customer\Entities\Practice;
use GenerateToledoSignatures;
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
        $practiceName = \Illuminate\Support\Facades\App::environment(['testing', 'staging', 'review', 'local'])
            ? GenerateToledoSignatures::TOLEDO_DEMO
            : GenerateToledoSignatures::TOLEDO_CLINIC;

        if (GenerateToledoSignatures::TOLEDO_DEMO === $practiceName) {
            Practice::firstOrCreate(
                [
                    'name' => GenerateToledoSignatures::TOLEDO_DEMO,
                ],
                [
                    'active'                => 1,
                    'display_name'          => 'Toledo Demo',
                    'is_demo'               => 1,
                    'clh_pppm'              => 0,
                    'term_days'             => 30,
                    'outgoing_phone_number' => 2025550196,
                ]
            );
        }

        \Excel::import(new UpdateProvidersFromExcel($practiceName), 'storage/toledo-provider-signatures/pcp_signature_file_for_clh.xlsx');
    }
}
