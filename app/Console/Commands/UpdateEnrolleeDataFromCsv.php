<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Enrollee;
use App\Services\GoogleDrive;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Storage;

class UpdateEnrolleeDataFromCsv extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates enrollee data from CSV containing all enrollees from the English Enrollment Sheet';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:updateFromCsv';

    private $googleDrive;

    /**
     * Create a new command instance.
     *
     * @param GoogleDrive $googleDrive
     */
    public function __construct(GoogleDrive $googleDrive)
    {
        parent::__construct();
        $this->googleDrive = $googleDrive;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $contents = collect(Storage::drive('google')
//                                   ->listContents('/', true));
        $file = collect(Storage::drive('google')
            ->listContents('/', true))
            ->where('filename', '=', 'English Enrollment Records All Time - Sheet2')
            ->first();

        if ( ! $file) {
            throw new \Exception('File not found', 500);
        }

        $stream = $this->googleDrive->getFilesystemHandle()
            ->getDriver()
            ->readStream($file['path']);

        $localDisk  = Storage::disk('local');
        $fileName   = $file['basename'];
        $pathToFile = storage_path("app/${fileName}");
        $localDisk->put($fileName, $stream);

        $iterator = read_file_using_generator($pathToFile);

        $headers = [];
        $data    = [];

        $i = 1;
        foreach ($iterator as $iteration) {
            if ( ! $iteration) {
                continue;
            }
            if (1 == $i) {
                $headers = str_getcsv($iteration, ',');
                ++$i;
                continue;
            }
            $row = [];
            foreach (str_getcsv($iteration) as $key => $field) {
                $row[$headers[$key]] = $field;
            }
            $row    = array_filter($row);
            $data[] = $row;
        }

        $csv = collect($data)->filter(function ($row) {
            return array_key_exists('Call_Status', $row);
        });

        Enrollee::whereIn('id', $csv->pluck('Eligible_Patient_ID')->toArray())
            ->orWhereIn('eligibility_job_id', $csv->pluck('Eligibility_Job_ID')->toArray())
            ->chunk(200, function ($enrollees) use (&$csv) {
                $enrollees->each(function (Enrollee $e) use ($csv) {
                    $row = $csv->filter(function ($row) use ($e) {
                        //We need either the enrollee id, or the eligibility job id
                        if (array_key_exists('Eligible_Patient_ID', $row)) {
                            return $row['Eligible_Patient_ID'] == $e->id;
                        }
                        if (array_key_exists('Eligibility_Job_ID', $row)) {
                            return $row['Eligibility_Job_ID'] == $e->eligibility_job_id;
                        }

                        return false;
                    })->first();

                    if ($row) {
                        $e = $this->setEnrolleeStatus($e, $row);
                        if (array_key_exists('Call_Date', $row) && ! empty($row['Call_Date'])) {
                            $date = preg_split("/[.|\/]/", $row['Call_Date']);
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
                    //in case we have the same patient but with different call status or call dates, we want to forget the one we update, because we are using ->first() above.
                    //also this will help with memory
                    $csv->forget($row);
                });
            });

        $localDisk->delete($fileName);
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
