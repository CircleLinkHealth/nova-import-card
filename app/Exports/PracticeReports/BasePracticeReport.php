<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use App\Contracts\Reports\PracticeDataExportInterface;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

abstract class BasePracticeReport implements FromQuery, WithMapping, PracticeDataExportInterface, WithHeadings, ShouldQueue, Mediable
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

    abstract public function filename(): string;

    public function forPractice(int $practiceId): PracticeDataExportInterface
    {
        if ( ! $this->practice) {
            $this->practice = Practice::findOrFail($practiceId);
        }

        return $this;
    }

    /**
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

    public function fullPath(): string
    {
        if ( ! $this->fullPath) {
            $this->fullPath = $this->getTempStorage()->path($this->filename);
        }

        return $this->fullPath;
    }

    public function getSignedLink(): string
    {
        return $this->signedLink;
    }

    public function getTempStorage(): FilesystemAdapter
    {
        return Storage::disk(self::STORE_TEMP_REPORT_ON_DISK);
    }

    abstract public function headings(): array;

    /**
     * @param mixed $row
     */
    abstract public function map($row): array;

    /**
     * The name of the Media Collection.
     */
    abstract public function mediaCollectionName(): string;

    abstract public function query(): Builder;
}
