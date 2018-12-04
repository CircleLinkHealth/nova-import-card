<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands\Athena;

use App\ForeignId;
use App\Models\CCD\CcdVendor;
use App\Services\AthenaAPI\CreateAndPostPdfCareplan;
use Illuminate\Console\Command;

class GetCcds extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get CCDs from Athena, if there are any CCDA Requests.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getCcds';

    private $service;

    /**
     * Create a new command instance.
     */
    public function __construct(CreateAndPostPdfCareplan $athenaApi)
    {
        parent::__construct();

        $this->service = $athenaApi;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $vendors = CcdVendor::whereEhrName(ForeignId::ATHENA)->get();

        foreach ($vendors as $vendor) {
            $this->service->getCcdsFromRequestQueue(5);
        }
    }
}
