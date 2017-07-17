<?php

namespace App\Console\Commands\Athena;

use App\ForeignId;
use App\Models\CCD\CcdVendor;
use App\Services\AthenaAPI\Service;
use Illuminate\Console\Command;
use Maknz\Slack\Facades\Slack;

class GetCcds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getCcds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get CCDs from Athena, if there are any CCDA Requests.';

    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Service $athenaApi)
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

        if (app()->environment('worker')) {
//            Slack::to('#background-tasks')
//                ->send("Grabbing CCDs from Athena CCDA request queue. \n");
        }


    }
}
