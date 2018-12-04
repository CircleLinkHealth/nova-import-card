<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/22/2018
 * Time: 7:25 PM
 */

namespace App\Contracts;

interface EligibilityProcessable
{
    /**
     * Queue a file to process for eligibility.
     */
    public function queue();

    /**
     * Process a csv from the `cloud` s3 disk
     */
    public function processEligibility();

    /**
     * Return the file from S3 or Local storage.
     */
    public function getFilePath();
}
