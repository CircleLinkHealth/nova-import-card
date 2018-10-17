<?php

namespace App\Console\Commands;

use App\EligibilityJob;
use App\ProcessedFile;
use App\Services\CCD\ProcessEligibilityService;
use App\WT1CsvParser;
use Illuminate\Console\Command;

class ImportWT1Csv extends Command
{

    /**
     * @var ProcessEligibilityService
     */
    private $processEligibilityService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wt1:importCsv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import WT1 CSV from /cryptdata/var/sftp/sftp1/files/';

    /**
     * Create a new command instance.
     *
     * @param ProcessEligibilityService $processEligibilityService
     */
    public function __construct(ProcessEligibilityService $processEligibilityService)
    {
        parent::__construct();
        $this->processEligibilityService = $processEligibilityService;
    }

    /**
     * Execute the console command.
     * TODO
     *
     * @return mixed
     */
    public function handle()
    {
        $count = 0;

        foreach (\Storage::disk('ccdas')->files() as $fileName) {
            if (stripos($fileName, 'circlelink_wt1_') === false) {
                continue;
            }

            $path = config('filesystems.disks.ccdas.root') . '/' . $fileName;

            $exists = ProcessedFile::wherePath($path)->first();

            if ($exists) {
                \Log::info("Already processed $path");

                continue;
            }

            $parser = new WT1CsvParser();
            $parser->parseFile($fileName);
            $patients = $parser->toArray();

            if (count($patients) == 0) {
                $this->info("Could not get any patients from $fileName");
                continue;
            }

            //todo: Practice id for WT1
            $practice = new \stdClass();
            $practice->id = 999;
            $practice->name = 'WT1';

            $batch = $this->processEligibilityService->createClhMedicalRecordTemplateBatch(
                'ccdas',
                $path,
                $practice->id,
                true,
                false,
                true);

            foreach ($patients as $p) {
                $this->createEligibilityJob($p, $practice, $batch->id);
            }

            $this->info("Create Medical Record Template Batch for: $fileName");

            $count++;

            if ($count == 4) {
                break;
            }
        }
    }

    private function createEligibilityJob($p, $practice, $batchId)
    {
        $hash = $practice->name . $p['first_name'] . $p['last_name'] . $p['mrn'] . $p['city'] . $p['state'] . $p['zip'];

        $job = EligibilityJob::whereHash($hash)->first();

        if ( ! $job) {
            $job = EligibilityJob::create([
                'batch_id' => $batchId,
                'hash'     => $hash,
                'data'     => $p,
            ]);
        }

        return $job;
    }
}
