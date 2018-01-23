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
use App\Jobs\ProcessEligibilityProcessable;
use App\Models\MedicalRecords\Ccda;
use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
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
        $directory = storage_path($this->relativeDirectory);

        foreach (glob("$directory/*xml") as $filePath) {
            $ccda = Ccda::create([
                'source'      => 'uploaded',
                'imported'    => false,
                'xml'         => file_get_contents($filePath),
                'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                'practice_id' => $this->practice->id,
            ]);

            $filePath = str_replace(storage_path(), '', $filePath);

            $deleted = \Storage::disk('storage')->delete($filePath);

            ProcessCcda::withChain([
                new CheckCcdaEnrollmentEligibility($ccda->id, $this->practice, $this->filterLastEncounter, $this->filterInsurance, $this->filterProblems)
            ])->dispatch($ccda);
        }
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

        $dir = storage_path($this->relativeDirectory);

        $zip = ZipFacade::open($path);
        $zip->extract($dir);

        $deleted = \Storage::disk('storage')->delete(str_replace(storage_path(), '', $path));

        return $dir;
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
            $dir      = storage_path($relDir);
            $fileName = 'unzip-' . $this->practice->name . '-' . Carbon::now()->toTimeString() . '.zip';

            $dirPerms = mkdir($dir, 0775, true);

            $this->getFile()->move($dir, $fileName);

            $changed = \Storage::disk('storage')->setVisibility("$relDir/$fileName",'public');

            $this->setFile("$dir/$fileName");

            $changeFileNamePerms = chmod("$dir/$fileName", 0775);

            $this->relativeDirectory = $relDir;

            $this->unzip();
        }

        parent::queue();
    }
}