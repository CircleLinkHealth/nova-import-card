<?php

namespace App\Console\Commands;

use App\Models\MedicalRecords\Ccda;
use Illuminate\Console\Command;

class SplitMergedCcdas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccdas:split-merged';

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

        foreach (\Storage::disk('ccdas')->files() as $fileName) {
            if (stripos($fileName, '.xml') !== false) {
                $xmlFiles[] = $fileName;

                $exploded = explode('</ClinicalDocument>', \Storage::disk('ccdas')->get($fileName));

                foreach ($exploded as $ccdaString) {
                    if (stripos($ccdaString, '<ClinicalDocument') !== false) {
                        $ccdas[] = Ccda::create([
                            'source'   => Ccda::SFTP_DROPBOX,
                            'imported' => false,
                            'xml'      => $ccdaString,
                        ]);
                    }
                }


                $newPath = 'done/' . str_replace('.xml', '.processed', $fileName);
                \Storage::disk('ccdas')->move($fileName, $newPath);
            }
        }

        $xmlCount = count($xmlFiles);
        $ccdaCount = count($ccdas);

        $this->info("$xmlCount XML files found. $ccdaCount CCDAs created.");
    }
}
