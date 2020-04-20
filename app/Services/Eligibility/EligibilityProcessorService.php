<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Eligibility;

use App\Services\Eligibility\Processables\Zip;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Http\UploadedFile;

class EligibilityProcessorService
{
    /**
     * @param bool $filterLastEncounter
     * @param bool $filterInsurance
     * @param bool $filterProblems
     * @param bool $createEnrollees
     *
     * @throws \Exception
     *
     * @return
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
            $processor = new Zip(
                $uploadedFile,
                $practice,
                $filterLastEncounter,
                $filterInsurance,
                $filterProblems,
                $createEnrollees
            );
        }

        $processor->queue();

        return 'You have queued some files to determine eligibility. You will receive a notification when the processing completes. You may now close this page.';
    }
}
