<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Enrollee;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EnroleeStatus implements WithChunkReading, ToModel, WithHeadings, ShouldQueue
{
    use Importable;

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 200;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Eligible_Patient_ID',	'Eligibility_Job_ID',	'Call_Status',	'Call_Date',
        ];
    }

    public function model(array $row)
    {
        if (array_key_exists('Call_Status', $row)) {
            $e = Enrollee::where('id', $row['Eligible_Patient_ID'])
                ->orWhere('Eligibility_Job_ID', $row['Eligibility_Job_ID'])
                ->orWhere('Eligibility_Job_ID', $row['Eligibility_Job_ID'])
                ->first();

            if ($e) {
                $e = $this->setEnrolleeStatus($e, $row);
                if (array_key_exists('call_date', $row) && ! empty($row['call_date'])) {
                    //this is a hack helping us parse dates from the csv that combine dots and dashes. It makes alot of assumptions that in other cases may be wrong. Fix if needed.
                    $date = preg_split("/[.|\/]/", $row['call_date']);
                    if (3 == count($date)) {
                        try {
                            $e->last_attempt_at = Carbon::parse("{$date[0]}/{$date[1]}/{$date[2]}");
                        } catch (\Exception $exception) {
                            //do nothing, date provided in csv is invalid
                        }
                    }
                }

                return $e;
            }

            return null;
        }
    }

    private function setEnrolleeStatus($e, $row)
    {
        if (str_contains(strtolower($row['Call_Status']), ['maybe', 'attempt', '3', '2', '1', 'soft'])) {
            if (str_contains($row['Call_Status'], '3')) {
                $e->attempt_count = 3;
            }
            if (str_contains($row['Call_Status'], '2')) {
                $e->attempt_count = 2;
            }
            if (str_contains($row['Call_Status'], '1')) {
                $e->attempt_count = 1;
            }
            $e->status = Enrollee::SOFT_REJECTED;
        }
        if (str_contains(strtolower($row['Call_Status']), ['hard', 'declined'])) {
            $e->status = Enrollee::REJECTED;
        }
        if (str_contains(strtolower($row['Call_Status']), 'reach')) {
            $e->status = Enrollee::UNREACHABLE;
        }
        if (str_contains(strtolower($row['Call_Status']), 'call')) {
            $e->status = Enrollee::TO_CALL;
        }
        if (str_contains(strtolower($row['Call_Status']), 'enrolled')) {
            $e->status = Enrollee::ENROLLED;
        }

        return $e;
    }
}
