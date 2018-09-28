<?php

namespace App\Console\Commands;

use App\ProcessedFile;
use App\Services\CCD\ProcessEligibilityService;
use Illuminate\Console\Command;

class ImportWT1Ccds extends Command
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
    protected $signature = 'wt1:importCcds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import WT1 CCDs from /cryptdata/var/sftp/sftp1/files/';

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

            //todo: Practice id for WT1
            $batch = $this->processEligibilityService->createClhMedicalRecordTemplateBatch(
                'ccdas',
                $path,
                999,
                true,
                false,
                true);

            $this->info("Create Medical Record Template Batch for: $fileName");

            $count++;

            if ($count == 4) {
                break;
            }
        }
    }
}
