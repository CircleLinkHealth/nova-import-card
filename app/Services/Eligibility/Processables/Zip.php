<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Eligibility\Processables;

use CircleLinkHealth\Eligibility\Jobs\CheckCcdaEnrollmentEligibility;
use CircleLinkHealth\Eligibility\Jobs\ProcessCcda;
use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use ZanySoft\Zip\ZipFacade;

class Zip extends BaseProcessable
{
    public $relativeDirectory;

    /**
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function processEligibility()
    {
        foreach (\Storage::disk('google')->files($this->relativeDirectory) as $filePath) {
            $ccda = Ccda::create([
                'source'   => 'uploaded',
                'xml'      => \Storage::disk('google')->get($filePath),
                'status'   => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                'imported' => false,
            ]);

            //for some reason it doesn't save practice_id when using Ccda::create([])
            $ccda->practice_id = (int) $this->practice->id;
            $ccda->save();

            $deleted = \Storage::disk('google')->delete($filePath);

            ProcessCcda::withChain([
                new CheckCcdaEnrollmentEligibility(
                    $ccda->id,
                    $this->practice,
                    $this->filterLastEncounter,
                    $this->filterInsurance,
                    $this->filterProblems
                ),
            ])->dispatch($ccda->id)
                ->onQueue('low');
        }
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function queue()
    {
        if (is_a($this->getFilePath(), UploadedFile::class) || is_a($this->getFilePath(), File::class)) {
            $date     = Carbon::now();
            $relDir   = "{$date->toDateString()}/unzip/{$this->practice->name}/{$date->toTimeString()}";
            $fileName = 'unzip-'.$this->practice->name.'-'.Carbon::now()->toTimeString().'.zip';

            \Storage::disk('local')->putFileAs($relDir, new File($this->getFilePath()), $fileName);

            $this->setFile("$relDir/$fileName");

            $this->relativeDirectory = $relDir;

            $this->unzip();
        }

        parent::queue();
    }

    /**
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return mixed
     */
    public function unzip()
    {
        $disk      = \Storage::disk('local');
        $cloudDisk = \Storage::disk('google');
        $prefix    = $disk->getAdapter()->getPathPrefix();

        $path            = $this->getFilePath();
        $fullZipFilePath = "$prefix$path";

        if ( ! file_exists($fullZipFilePath)) {
            throw new \Exception('File does not exist.');
        }

        if ( ! ZipFacade::check($fullZipFilePath)) {
            throw new \Exception('Invalid zip file.');
        }

        $dir = $prefix."$this->relativeDirectory";

        $zip = ZipFacade::open($fullZipFilePath);
        $zip->extract($dir);

        $xmlFiles = glob("$dir/*xml");

        if (count($xmlFiles) < 1) {
            throw new \Exception('No files were extracted. This could be due to an error, or the archive was empty.');
        }

        foreach ($xmlFiles as $filePath) {
            if ( ! file_exists($filePath)) {
                throw new \Exception('File not found');
            }

            $saved = $cloudDisk
                ->put($this->relativeDirectory.'/'.Carbon::now()->toAtomString().'.xml', fopen($filePath, 'r+'));

            $deleted = $disk->delete(str_replace($prefix, '', $filePath));
        }

        if (count($cloudDisk->files($this->relativeDirectory)) < 1) {
            throw new \Exception('No files were saved to cloud storage.');
        }

        $deleted = $disk->delete($path);

        return $dir;
    }
}
