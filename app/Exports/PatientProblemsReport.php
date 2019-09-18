<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Contracts\Reports\PracticeDataExport;
use App\Models\CCD\Problem;
use App\Notifications\SendSignedUrlToDownloadPatientProblemsReport;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use URL;

class PatientProblemsReport implements FromQuery, WithMapping, PracticeDataExport, WithHeadings
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
                $mediaCollectionName = "{$this->practice->name}_patients_with_problems_reports";
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
            $this->filename = "patients_with_problems_report_generated_at_$generatedAt.csv";
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
    public function headings(): array
    {
        return [
            'Name',
            'MRN',
            'DOB',
            'Condition',
            'ICD-10',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $demographics = [
            'name' => $row->display_name,
            'mrn'  => $row->getMrnNumber(),
            'dob'  => $row->patientInfo->dob(),
        ];

        if ($row->ccdProblems->isEmpty()) {
            return array_merge(
                $demographics,
                [
                    'condition' => 'N/A',
                    'icd10'     => 'N/A',
                ]
            );
        }

        return $row->ccdProblems->map(
            function (Problem $problem) use ($demographics) {
                return array_merge(
                    $demographics,
                    [
                        'condition' => $problem->name,
                        'icd10'     => $problem->icd10Code() ?? 'N/A',
                    ]
                );
            }
        )->all();
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

        $this->user->notify(new SendSignedUrlToDownloadPatientProblemsReport($this->signedLink));

        return $this;
    }

    public function query()
    {
        return User::ofPractice($this->practice)
            ->ofType('participant')
            ->has('patientInfo')
            ->with(
                [
                    'patientInfo' => function ($q) {
                        $q->select('mrn_number', 'birth_date', 'id', 'user_id');
                    },
                    'ccdProblems' => function ($q) {
                        $q->select('id', 'name', 'cpm_problem_id', 'patient_id')->with(
                            ['icd10Codes', 'cpmProblem']
                        );
                    },
                ]
            )->select('id', 'display_name');
    }
}
