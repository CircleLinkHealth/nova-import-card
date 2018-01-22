<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/22/2018
 * Time: 5:50 PM
 */

namespace App\Services\Eligibility\Processables;

use App\Services\WelcomeCallListGenerator;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;


class Csv extends BaseProcessable
{
    /**
     * @return WelcomeCallListGenerator
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function processEligibility()
    {
        $csv         = parseCsvToArray($this->getFile());
        $patientList = new Collection($csv);

        $list = new WelcomeCallListGenerator(
            $patientList,
            $this->filterLastEncounter,
            $this->filterInsurance,
            $this->filterProblems,
            $this->createEnrollees,
            $this->practice
        );

        return $list;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function queue()
    {
        if (is_a($this->getFile(), UploadedFile::class) || is_a($this->getFile(), File::class)) {
            $fileName = 'process-eligibility-' . $this->practice->name . '-' . Carbon::now()->toTimeString() . '.csv';
            $date     = Carbon::now()->toDateString();

            $this->getFile()->move(storage_path($date), $fileName);
            \Storage::disk('storage')->setVisibility("$date/$fileName",'public');

            $this->setFile(storage_path("$date/$fileName"));
        }

        parent::queue();
    }
}