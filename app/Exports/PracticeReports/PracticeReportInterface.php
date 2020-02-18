<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use App\Contracts\Reports\PracticeDataExportInterface;
use App\Notifications\SendSignedUrlToDownloadPracticeReport;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use URL;

abstract class PracticeReportInterface implements FromQuery, WithMapping, PracticeDataExportInterface, WithHeadings, ShouldQueue, Mediable
{
    use Exportable;
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var string
     */
    protected $fullPath;

    /**
     * @var Media
     */
    protected $media;
    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var string
     */
    protected $signedLink;

    /**
     * @var User
     */
    protected $user;

    /**
     * @return string
     */
    abstract public function filename(): string;
    
    /**
     * The name of the Media Collection
     * @return string
     */
    abstract public function mediaCollectionName(): string;

    /**
     * @param int $practiceId
     *
     * @return PracticeDataExportInterface
     */
    public function forPractice(int $practiceId): PracticeDataExportInterface
    {
        if ( ! $this->practice) {
            $this->practice = Practice::findOrFail($practiceId);
        }

        return $this;
    }

    /**
     * @param int $userId
     *
     * @return PracticeDataExportInterface
     * @throws \Exception
     */
    public function forUser(int $userId): PracticeDataExportInterface
    {
        if ( ! $this->practice) {
            throw new \Exception('Please call forPractice and provide valid practice first');
        }
        if ( ! $this->user) {
            $this->user = User::ofPractice($this->practice)->where('id', $userId)->firstOrFail();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function fullPath(): string
    {
        if ( ! $this->fullPath) {
            $this->fullPath = $this->getTempStorage()->path($this->filename);
        }

        return $this->fullPath;
    }

    /**
     * @return string
     */
    public function getSignedLink(): string
    {
        return $this->signedLink;
    }

    /**
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    public function getTempStorage(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return \Storage::disk(self::STORE_TEMP_REPORT_ON_DISK);
    }

    /**
     * @return array
     */
    abstract public function headings(): array;

    /**
     * @param mixed $row
     *
     * @return array
     */
    abstract public function map($row): array;

    /**
     * @return Builder
     */
    abstract public function query(): Builder;

}
