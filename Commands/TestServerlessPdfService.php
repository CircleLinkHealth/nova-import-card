<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Commands;

use CircleLinkHealth\PdfService\Services\PdfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestServerlessPdfService extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes a dummy request to test pdf service';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:serverless-pdf-service';

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
     * @return int
     */
    public function handle()
    {
        $storage    = Storage::drive('storage');
        $pdfService = app(PdfService::class);
        $path       = $pdfService->blankPage('pdf1.php');
        $this->info("Blank Page generated: $path");

        $path2  = $pdfService->blankPage('pdf2.php');
        $merged = $pdfService->mergeFiles([$path, $path2], $storage->path('pds/pdf_merged.php'));
        $count  = $pdfService->countPages($merged);
        $this->info("Count of merged: $count");

        return 0;
    }
}
