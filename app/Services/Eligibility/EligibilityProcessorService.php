<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/22/2018
 * Time: 5:32 PM
 */

namespace App\Services\Eligibility;


use App\Practice;
use App\Services\Eligibility\Processables\Csv;
use App\Services\Eligibility\Processables\Zip;
use Illuminate\Http\UploadedFile;

class EligibilityProcessorService
{
    /**
     * @param UploadedFile $uploadedFile
     * @param Practice $practice
     * @param bool $filterLastEncounter
     * @param bool $filterInsurance
     * @param bool $filterProblems
     * @param bool $createEnrollees
     *
     * @return
     * @throws \Exception
     */
    public function processEligibility(
        UploadedFile $uploadedFile,
        Practice $practice,
        $filterLastEncounter = true,
        $filterInsurance = true,
        $filterProblems = true,
        $createEnrollees = false
    ) {
        $fileExtension = $uploadedFile->clientExtension();

        if (in_array($fileExtension, ['zip'])) {
            $processor = new Zip($uploadedFile, $practice, $filterLastEncounter, $filterInsurance, $filterProblems,
                $createEnrollees);
        }

        $processor->queue();

        return 'You have queued some files to determine eligibility. You will receive a notification when the processing completes. You may now close this page.';
    }
}