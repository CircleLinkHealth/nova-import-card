<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use App\Contracts\Reports\PracticeDataExport;
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

abstract class PracticeReport implements FromQuery, WithMapping, PracticeDataExport, WithHeadings, ShouldQueue
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
     * @param null $mediaCollectionName
     *
     * @return PracticeDataExport
     */
    abstract public function createMedia($mediaCollectionName = null): PracticeDataExport;

    /**
     * @return string
     */
    abstract public function filename(): string;

    /**
     * @param int $practiceId
     *
     * @return PracticeDataExport
     */
    public function forPractice(int $practiceId): PracticeDataExport
    {
        if ( ! $this->practice) {
            $this->practice = Practice::findOrFail($practiceId);
        }

        return $this;
    }

    /**
     * @param int $userId
     *
     * @return PracticeDataExport
     * @throws \Exception
     */
    public function forUser(int $userId): PracticeDataExport
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
        return \Storage::disk('local');
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
     * @return mixed
     */
    public function notifyUser(): PracticeDataExport
    {
        if ( ! is_a($this->media, Media::class) || ! $this->media->id || ! is_a($this->user,
                User::class) || ! $this->user->id) {
            return false;
        }

        $this->signedLink = URL::temporarySignedRoute('download.media.from.signed.url',
            now()->addDays(self::EXPIRES_IN_DAYS), [
                'media_id'    => $this->media->id,
                'user_id'     => $this->user->id,
                'practice_id' => $this->practice->id,
            ]);

        $this->user->notify(new SendSignedUrlToDownloadPracticeReport(get_called_class(), $this->signedLink, $this->practice->id, $this->media->id));

        return $this;
    }

    /**
     * @return Builder
     */
    abstract public function query(): Builder;

}
