<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class PracticeCallsReport extends BasePracticeReport
{
    public function filename(): string
    {
        if ( ! $this->filename) {
            $this->filename = $this->mediaCollectionName();
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

    public function mediaCollectionName(): string
    {
        $generatedAt = now()->toDateTimeString();

        return "practice_calls_last_three_months_generated_at_$generatedAt.csv";
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
