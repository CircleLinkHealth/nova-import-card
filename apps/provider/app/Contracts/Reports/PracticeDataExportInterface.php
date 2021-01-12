<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Reports;

use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;

interface PracticeDataExportInterface
{
    /**
     * Company policy for one time reports to expire in two days.
     */
    const EXPIRES_IN_DAYS = 2;
    /**
     * This is the path to store the temporary report while it's created. Once report is fully generated, it will be
     * attached as Media to the Practice.
     */
    const STORE_TEMP_REPORT_ON_DISK = 'media';

    /**
     * Practice whose data we are getting.
     *
     * @return PracticeDataExportInterface
     */
    public function forPractice(int $practiceId): self;

    /**
     * User we are making report available to.
     *
     * @return PracticeDataExportInterface
     */
    public function forUser(int $userId): self;

    /**
     * Get the disk we're storing our temporary file on.
     */
    public function getTempStorage(): \Illuminate\Filesystem\FilesystemAdapter;

    /**
     * Make user there is a store method.
     * Make sure you use Maatwebsite\Excel\Concerns\Exportable;.
     *
     * @param string $filePath
     * @param mixed  $diskOptions
     *
     * @throws NoFilePathGivenException
     *
     * @return bool|PendingDispatch
     */
    public function queue(string $filePath = null, string $disk = null, string $writerType = null, $diskOptions = []);

    /**
     * Make user there is a store method.
     * Make sure you use Maatwebsite\Excel\Concerns\Exportable;.
     *
     * @param string $filePath
     * @param mixed  $diskOptions
     *
     * @throws NoFilePathGivenException
     *
     * @return bool|PendingDispatch
     */
    public function store(string $filePath = null, string $disk = null, string $writerType = null, $diskOptions = []);
}
