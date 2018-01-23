<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/22/2018
 * Time: 5:50 PM
 */

namespace App\Services\Eligibility\Processables;


use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\ProcessCcda;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
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
        foreach (\Storage::disk('cloud')->files($this->relativeDirectory) as $filePath) {
            $ccda = Ccda::create([
                'source'      => 'uploaded',
                'imported'    => false,
                'xml'         => \Storage::disk('cloud')->get($filePath),
                'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                'practice_id' => $this->practice->id,
            ]);

//            $filePath = str_replace(storage_path(), '', $filePath);
//            $deleted = \Storage::disk('cloud')->delete($filePath);

            ProcessCcda::withChain([
                new CheckCcdaEnrollmentEligibility($ccda->id, $this->practice, $this->filterLastEncounter,
                    $this->filterInsurance, $this->filterProblems),
            ])->dispatch($ccda->id);
        }
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function queue()
    {
        if (is_a($this->getFile(), UploadedFile::class) || is_a($this->getFile(), File::class)) {
            $date     = Carbon::now();
            $relDir   = "{$date->toDateString()}/unzip/{$this->practice->name}/{$date->toTimeString()}";
            $fileName = 'unzip-' . $this->practice->name . '-' . Carbon::now()->toTimeString() . '.zip';

            \Storage::disk('ccdas')->putFileAs($relDir, new File($this->getFile()), $fileName);

            $this->setFile("$relDir/$fileName");

            $this->relativeDirectory = $relDir;

            $this->unzip();
        }

        parent::queue();
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function unzip()
    {
        $path = $this->getFile()->path();

        if ( ! file_exists($path)) {
            throw new \Exception('File does not exist.');
        }

        if ( ! ZipFacade::check($path)) {
            throw new \Exception('Invalid zip file.');
        }
        
        $storage = \Storage::disk('ccdas');

        $prefix = $storage->getAdapter()->getPathPrefix();

        $dir = $prefix."$this->relativeDirectory";

        $zip = ZipFacade::open($path);
        $zip->extract($dir);

        if (count($storage->files($this->relativeDirectory)) < 1) {
            throw new \Exception('No files were extracted. This could be due to an error, or the archive was empty.');
        }

        foreach ($storage->files($this->relativeDirectory) as $filePath) {
            $file = new File("$prefix$filePath");

            if (!file_exists("$prefix$filePath")) {
                throw new \Exception('File not found');
            }

            $saved = \Storage::disk('cloud')
                             ->putFileAs($this->relativeDirectory, $file, Carbon::now()->toAtomString() . '.xml');

            $deleted = $storage->delete($filePath);
        }

        $deleted = $storage->delete($path);

        return $dir;
    }
}