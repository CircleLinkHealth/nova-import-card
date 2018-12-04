<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\ProcessedFile;
use Illuminate\Console\Command;

class ImportLGHInsurance extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import LGH Insurance files from /cryptdata/var/sftp/sftp1/files/';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lgh:importInsurance';

    /**
     * Create a new command instance.
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
        $ccdas    = [];
        $xmlFiles = [];

        $count = 0;

        foreach (\Storage::disk('ccdas')->files() as $fileName) {
            if (false === stripos($fileName, 'circlelink_supplement_')) {
                continue;
            }

            $path = config('filesystems.disks.ccdas.root').'/'.$fileName;

            $exists = ProcessedFile::wherePath($path)->first();

            if ($exists) {
                \Log::info("Already processed ${path}");

                continue;
            }

            $job = (new \App\Jobs\ImportLGHInsurance($fileName))->onQueue('low');

            dispatch($job);

            $this->info("Queued Job to import: ${fileName}");

            ++$count;

            if (4 == $count) {
                break;
            }
        }
    }
}
