<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Enrollee;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateEnrolleesFromEnglishEnrollmentSheetCsv implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $csv;

    /**
     * Create a new job instance.
     */
    public function __construct(array $csv)
    {
        $this->csv = $csv;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $csv = collect($this->csv)->filter(function ($row) {
            return array_key_exists('call_status', $row);
        });

        Enrollee::whereIn('id', $csv->pluck('eligible_patient_id')->toArray())
            ->orWhereIn('eligibility_job_id', $csv->pluck('eligibility_job_id')->toArray())
            ->chunk(200, function ($enrollees) use (&$csv) {
                    $enrollees->each(function (Enrollee $e) use ($csv) {
                        $row = $csv->filter(function ($row) use ($e) {
                            //We need either the enrollee id, or the eligibility job id
                            if (array_key_exists('eligible_patient_id', $row)) {
                                return $row['eligible_patient_id'] == $e->id;
                            }
                            if (array_key_exists('eligibility_job_id', $row)) {
                                return $row['eligibility_job_id'] == $e->eligibility_job_id;
                            }

                            return false;
                        })
                            //use last to get the last row for that patient from the csv (latest updates for that patient)
                            ->last();

                        if ($row) {
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
                            $e->save();
                        }
                    });
                });
    }

    private function setEnrolleeStatus($e, $row)
    {
        if (str_contains(strtolower($row['call_status']), ['maybe', 'attempt', '3', '2', '1', 'soft'])) {
            if (str_contains($row['call_status'], '3')) {
                $e->attempt_count = 3;
            }
            if (str_contains($row['call_status'], '2')) {
                $e->attempt_count = 2;
            }
            if (str_contains($row['call_status'], '1')) {
                $e->attempt_count = 1;
            }
            $e->status = Enrollee::SOFT_REJECTED;
        }
        if (str_contains(strtolower($row['call_status']), ['hard', 'declined'])) {
            $e->status = Enrollee::REJECTED;
        }
        if (str_contains(strtolower($row['call_status']), 'reach')) {
            $e->status = Enrollee::UNREACHABLE;
        }
        if (str_contains(strtolower($row['call_status']), 'call')) {
            $e->status = Enrollee::TO_CALL;
        }
        if (str_contains(strtolower($row['call_status']), 'enrolled')) {
            $e->status = Enrollee::ENROLLED;
        }

        return $e;
    }
}
