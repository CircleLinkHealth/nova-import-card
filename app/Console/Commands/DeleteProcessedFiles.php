<?php

namespace App\Console\Commands;

use App\ProcessedFile;
use Illuminate\Console\Command;

class DeleteProcessedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:processed-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all files that were processed by the application.';

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
        $ccdaPath = config('services.ccda.dropbox-path');

        foreach (ProcessedFile::get() as $file) {
            $path = str_replace($ccdaPath, '', $file->path);

            if (\Storage::disk('ccdas')->exists($path)) {
                $deleted = \Storage::disk('ccdas')->delete($path);

                if ($deleted) {
                    $file->delete();
                }
            }
        }
    }
}
