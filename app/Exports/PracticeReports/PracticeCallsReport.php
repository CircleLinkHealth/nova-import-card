<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use App\Call;
use App\Contracts\Reports\PracticeDataExport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class PracticeCallsReport extends PracticeReport
{
    /**
     * @param null $mediaCollectionName
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     */
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

    public function headings(): array
    {
        return [
            'Name',
            'MRN',
            'DOB',
            'Call Date and Time',
            'Was Successful',
        ];
    }

    /**
     * @param mixed $row
     */
    public function map($row): array
    {
        $demographics = [
            'name' => $row->display_name,
            'mrn'  => $row->getMrnNumber(),
            'dob'  => $row->patientInfo->dob(),
        ];

        return $row->inboundCalls->map(function ($call) use ($demographics) {
            $calledDate = Carbon::parse($call->called_date);

            return array_merge($demographics, [
                'date_of_call'   => $calledDate->toDateTimeString(),
                'was_successful' => Call::REACHED === $call->status
                    ? 'Y'
                    : 'N',
            ]);
        })->all();
    }

    public function query(): Builder
    {
        return User::ofPractice($this->practice)
            ->ofType('participant')
            ->has('patientInfo')
            ->whereHas('inboundCalls', function ($calls) {
                       $calls->calledLastThreeMonths();
                   })
            ->with(
                       [
                           'inboundCalls' => function ($calls) {
                               $calls->select('inbound_cpm_id', 'status', 'called_date')
                                   ->calledLastThreeMonths();
                           },
                           'patientInfo',
                       ]
                   )->select('id', 'display_name');
    }
}
