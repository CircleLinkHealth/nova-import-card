<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EnroleeStatus implements WithChunkReading, ToModel, WithHeadingRow, ShouldQueue
{
    use Importable;

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function model(array $row)
    {
        if (array_key_exists('call_status', $row)) {
            $e = Enrollee::where('id', $row['eligible_patient_id'])
                ->orWhere('eligibility_job_id', $row['eligibility_job_id'])
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
                            Log::error("Enrollee with id {$e->id} failed to update last_attempt_field");
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
        if (Str::contains(strtolower($row['call_status']), ['maybe', 'attempt', '3', '2', '1', 'soft'])) {
            if (Str::contains($row['call_status'], '3')) {
                $e->attempt_count = 3;
            }
            if (Str::contains($row['call_status'], '2')) {
                $e->attempt_count = 2;
            }
            if (Str::contains($row['call_status'], '1')) {
                $e->attempt_count = 1;
            }
            $e->status = Enrollee::SOFT_REJECTED;
        }
        if (Str::contains(strtolower($row['call_status']), ['hard', 'declined'])) {
            $e->status = Enrollee::REJECTED;
        }
        if (Str::contains(strtolower($row['call_status']), 'reach')) {
            $e->status = Enrollee::UNREACHABLE;
        }
        if (Str::contains(strtolower($row['call_status']), 'call')) {
            $e->status = Enrollee::TO_CALL;
        }
        if (Str::contains(strtolower($row['call_status']), 'enrolled')) {
            $e->status = Enrollee::ENROLLED;
        }

        return $e;
    }
}
