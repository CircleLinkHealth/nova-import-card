<?php

namespace App\Console\Commands;

use App\ProcessedFiles;
use Illuminate\Console\Command;

class SplitMergedCcdas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccda:split-merged';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search for files that contain many CCDAs, save each individual CCDA in the DB, and move the original batch file to another directory.';

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
            if (stripos($fileName, '.xml') == false) {
                continue;
            }

            $path = config('filesystems.disks.ccdas.root') . '/' . $fileName;

            $exists = ProcessedFiles::wherePath($path)->first();

            if ($exists) {
                \Log::info("Already processed $path");

                continue;
            }

            $job = (new \App\Jobs\SplitMergedCcdas($fileName))->onQueue('ccda-splitter');

            dispatch($job);

            $this->info("Queued Job to split: $fileName");

            $count++;

            if ($count == 4) {
                break;
            }
        }
    }
}
