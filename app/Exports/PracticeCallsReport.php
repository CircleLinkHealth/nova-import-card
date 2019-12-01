<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Contracts\Reports\PracticeDataExport;
use App\Notifications\SendSignedUrlToDownloadPracticeReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use URL;

class PracticeCallsReport implements FromQuery, WithMapping, PracticeDataExport, WithHeadings
{
    use Exportable;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var string
     */
    private $fullPath;

    /**
     * @var Media
     */
    private $media;
    /**
     * @var Practice
     */
    private $practice;
    /**
     * @var string
     */
    private $signedLink;

    /**
     * @var User
     */
    private $user;

    public function createMedia($mediaCollectionName = null): PracticeDataExport
    {
        if ( ! $this->media) {
            if ( ! $mediaCollectionName) {
                $mediaCollectionName = "{$this->practice->name}_practice_calls_reports";
            }

            $this->store($this->filename(), self::STORE_TEMP_REPORT_ON_DISK);

            $this->media = $this->practice->addMedia($this->fullPath())->toMediaCollection($mediaCollectionName);
        }

        return $this;
    }

    public function filename(): string
    {
        if ( ! $this->filename) {
            $generatedAt    = now()->toDateTimeString();
            $this->filename = "practice_calls_last_three_months_generated_at_$generatedAt.csv";
        }

        return $this->filename;
    }

    public function forPractice(int $practiceId): PracticeDataExport
    {
        if ( ! $this->practice) {
            $this->practice = Practice::findOrFail($practiceId);
        }

        return $this;
    }

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

    public function getTempStorage(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return \Storage::disk('local');
    }

    public function headings(): array
    {
        return [
            'Date of Call',
            'Time of Call',
            'Was Successful',
        ];
    }

    /**
     * @param mixed $row
     */
    public function map($row): array
    {
        return $row->inboundCalls->map(function ($call) {
            $calledDate = Carbon::parse($call->called_date);

            return [
                'date_of_call'   => $calledDate->toDateString(),
                'time_of_call'   => $calledDate->toTimeString(),
                'was_successful' => 'reached' === $call->status ? 'true' : 'false',
            ];
        })->all();
    }

    /**
     * @return mixed
     */
    public function notifyUser(): PracticeDataExport
    {
        if ( ! is_a($this->media, Media::class) || ! $this->media->id || ! is_a($this->user, User::class) || ! $this->user->id) {
            return false;
        }

        $this->signedLink = URL::temporarySignedRoute('download.media.from.signed.url', now()->addDays(self::EXPIRES_IN_DAYS), [
            'media_id'    => $this->media->id,
            'user_id'     => $this->user->id,
            'practice_id' => $this->practice->id,
        ]);

        $this->user->notify(new SendSignedUrlToDownloadPracticeReport($this->signedLink));

        return $this;
    }

    public function query()
    {
        return User::ofPractice($this->practice)
            ->ofType('participant')
            ->has('patientInfo')
            ->with(
                       [
                           'inboundCalls' => function ($calls) {
                               $calls->select('inbound_cpm_id', 'status', 'called_date')
                                   ->whereNotNull('called_date')
                                   ->where('called_date', '>=', Carbon::now()->subMonth(3)->startOfMonth()->startOfDay())
                                   ->where('called_date', '<=', Carbon::now()->endOfDay());
                           },
                       ]
                   )->select('id');
    }
}
