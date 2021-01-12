<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

interface EligibilityProcessable
{
    /**
     * Return the file from S3 or Local storage.
     */
    public function getFilePath();

    /**
     * Process a csv from the `cloud` s3 disk.
     */
    public function processEligibility();

    /**
     * Queue a file to process for eligibility.
     */
    public function queue();
}
