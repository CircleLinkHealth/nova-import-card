<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Commands;

use CircleLinkHealth\PdfService\Services\PdfService;
use CircleLinkHealth\SharedModels\Services\CarePlanGeneratorService;
use Illuminate\Console\Command;

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
    protected $signature = 'test:serverless-pdf-service {requesterId?} {patientId?}';

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
        if ($this->hasArgument('requesterId') && $this->hasArgument('patientId')) {
            $patientId = $this->argument('patientId');
            $this->info("Generating PDF for patient[$patientId]");
            $media = app(CarePlanGeneratorService::class)->pdfForUsers($this->argument('requesterId'), [$patientId], true);
            $this->info(json_encode($media));

            return 0;
        }

        $pdfService = app(PdfService::class);
        $path       = $pdfService->blankPage('pdf1.pdf');
        $this->info("Blank Page generated: $path");

        $path2  = $pdfService->blankPage('pdf2.pdf');
        $merged = $pdfService->mergeFiles([$path, $path2], storage_path('pdfs/pdf_merged.pdf'));
        $count  = $pdfService->countPages($merged);
        $this->info("Count of merged: $count");

        return 0;
    }
}
