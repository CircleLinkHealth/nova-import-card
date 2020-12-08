<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Imports\SelfEnrolment\ReadSelfEnrolmentCsvData;
use App\Jobs\ProcessSelfEnrolablesFromCollectionJob;
use App\Services\ProcessSelfEnrolmentCsvData;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class UpdateEnrolleeProvidersThatCreatedWrong extends Command
{
    const MARILLAC_NAME        = 'marillac-clinic-inc';
    const WRONG_PROVIDER_EMAIL = 'danbecker14@gmail.com';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Enrollees that got assign with wrong Provider. Also updates enrollee-status depending on pending letter/enrolment status!';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reprocess:marillac-self-enrolment-enrollees';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
//        @todo: Make an enpoint to upload data from Csv / Practice, in future iteration.
        $practice    = Practice::where('name', self::MARILLAC_NAME)->firstOrFail();
        $dataFromCsv = Excel::toCollection(new ReadSelfEnrolmentCsvData(), 'storage/selfEnrolment-templates/MarillacManuallyProcessEnrolmentPatients.csv')->flatten(1);

        if ($dataFromCsv->isEmpty()) {
            $this->error('Csv imported collection is empty');

            return;
        }

        $processCsvService = (new ProcessSelfEnrolmentCsvData());

        try {
            $dataFromCsvProcessed = $processCsvService->processCsvCollection($dataFromCsv);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return;
        }

        $dataFromCsvProcessed->each(function ($enrolleeIds, $providerName) use ($practice) {
            foreach ($enrolleeIds->chunk(100) as $chunk) {
                ProcessSelfEnrolablesFromCollectionJob::dispatch($chunk, $practice->id, $providerName);
            }
        });
    }
}
