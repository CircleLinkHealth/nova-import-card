<?php

namespace App\Console\Commands;

use App\ProcessedFiles;
use Illuminate\Console\Command;

class ImportLGHInsurance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lgh:importInsurance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import LGH Insurance files from /cryptdata/var/sftp/sftp1/files/';

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
        $ccdas = [];
        $xmlFiles = [];

        $count = 0;

        foreach (\Storage::disk('ccdas')->files() as $fileName) {
            if (stripos($fileName, 'circlelink_supplement_') === false) {
                continue;
            }

            $path = config('filesystems.disks.ccdas.root') . '/' . $fileName;

            $exists = ProcessedFiles::wherePath($path)->first();

            if ($exists) {
                \Log::info("Already processed $path");

                continue;
            }

            $job = (new \App\Jobs\ImportLGHInsurance($fileName))->onQueue('ccda-processor');

            dispatch($job);

            $this->info("Queued Job to import: $fileName");

            $count++;

            if ($count == 4) {
                break;
            }
        }
    }
}
