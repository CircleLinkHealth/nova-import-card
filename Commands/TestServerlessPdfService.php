<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Commands;

use CircleLinkHealth\PdfService\Services\PdfService;
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
        $pdfService = app(PdfService::class);
        $path       = $pdfService->blankPage();
        $this->info("Blank Page generated: $path");

        return 0;
    }
}
