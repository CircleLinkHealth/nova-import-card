<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use App\Contracts\Reports\PracticeDataExport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class PracticeCallsReport extends PracticeReport
{
    /**
     * @param null $mediaCollectionName
     *
     * @return PracticeDataExport
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

    /**
     * @return string
     */
    public function filename(): string
    {
        if ( ! $this->filename) {
            $generatedAt    = now()->toDateTimeString();
            $this->filename = "practice_calls_last_three_months_generated_at_$generatedAt.csv";
        }

        return $this->filename;
    }

    /**
     * @return array
     */
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
     *
     * @return array
     */
    public function map($row): array
    {
        return $row->inboundCalls->map(function ($call) {
            $calledDate = Carbon::parse($call->called_date);

            return [
                'date_of_call'   => $calledDate->toDateString(),
                'time_of_call'   => $calledDate->toTimeString(),
                'was_successful' => 'reached' === $call->status
                    ? 'true'
                    : 'false',
            ];
        })->all();
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return User::ofPractice($this->practice)
                   ->ofType('participant')
                   ->has('patientInfo')
                   ->with(
                       [
                           'inboundCalls' => function ($calls) {
                               $calls->select('inbound_cpm_id', 'status', 'called_date')
                                     ->whereNotNull('called_date')
                                     ->where('called_date', '>=',
                                         Carbon::now()->subMonth(3)->startOfMonth()->startOfDay())
                                     ->where('called_date', '<=', Carbon::now()->endOfDay());
                           },
                       ]
                   )->select('id');
    }
}
