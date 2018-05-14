<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/14/18
 * Time: 3:58 PM
 */

namespace App\Services\CCD;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Importer\Loggers\Allergy\NumberedAllergyFields;
use App\Importer\Loggers\Medication\NumberedMedicationFields;
use App\Importer\Loggers\Problem\NumberedProblemFields;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\ProcessCcda;
use App\Jobs\ProcessEligibilityFromGoogleDrive;
use App\Jobs\ProcessSinglePatientEligibility;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class ProcessEligibilityService
{
    public function fromGoogleDrive(EligibilityBatch $batch)
    {
        $dir                 = $batch->options['dir'];
        $filterLastEncounter = (boolean)$batch->options['filterLastEncounter'];
        $filterInsurance     = (boolean)$batch->options['filterInsurance'];
        $filterProblems      = (boolean)$batch->options['filterProblems'];

        $cloudDisk = Storage::cloud();

        $practice  = Practice::findOrFail($batch->practice_id);
        $recursive = false; // Get subdirectories also?
        $contents  = collect($cloudDisk->listContents($dir, $recursive));

        $processedDir = $contents->where('type', '=', 'dir')
                                 ->where('filename', '=', 'processed')
                                 ->first();

        if ( ! $processedDir) {
            $cloudDisk->makeDirectory("$dir/processed");

            $processedDir = collect($cloudDisk->listContents($dir, $recursive))
                ->where('type', '=', 'dir')
                ->where('filename', '=', 'processed')
                ->first();
        }

        // not supporting zip files for now
//        $zipFiles = $contents
//            ->where('type', '=', 'file')
//            ->where('mimetype', '=', 'application/zip')
//            ->map(function ($file) use (
//                $cloudDisk,
//                $practice,
//                $dir,
//                $filterLastEncounter,
//                $filterInsurance,
//                $filterProblems,
//                $processedDir,
//                $batch
//            ) {
//                $cloudFilePath = $file['path'];
//                $cloudFileName = $file['filename'];
//                $cloudDirName  = $file['dirname'];
//
//                if (str_contains($cloudFileName, ['processed'])) {
//                    $cloudDisk->move($cloudFilePath, "{$processedDir['path']}/{$cloudFileName}");
//
//                    return $file;
//                }
//
//                $localDisk = Storage::disk('local');
//
//                $stream = $cloudDisk->getDriver()
//                                    ->readStream($cloudFilePath);
//
//                $targetFile = "zip/$dir/$cloudFileName";
//
//                $localDisk->put($targetFile, stream_get_contents($stream));
//
//                $isValid = Zip::check($localDisk->path($targetFile));
//
//                if ($isValid) {
//                    $unzipDir = "zip/$dir/unzipped";
//
//                    $localDisk->makeDirectory($unzipDir);
//
//                    $zip = Zip::open($localDisk->path($targetFile));
//                    $zip->extract($localDisk->path($unzipDir));
//                    $zip->close();
//
//                    foreach ($localDisk->allFiles($unzipDir) as $path) {
//                        $now       = Carbon::now()->toAtomString();
//                        $randomStr = str_random();
//                        $put       = $cloudDisk->put($cloudDirName . "/$randomStr-$now",
//                            fopen($localDisk->path($path), 'r+'));
//
//                        $ccda = Ccda::create([
//                            'batch_id'    => $batch->id,
//                            'source'      => Ccda::GOOGLE_DRIVE . "_$dir",
//                            'xml'         => stream_get_contents(fopen($localDisk->path($path), 'r+')),
//                            'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
//                            'imported'    => false,
//                            'practice_id' => (int)$practice->id,
//                        ]);
//
//                        //for some reason it doesn't save practice_id when using Ccda::create([])
//                        $ccda->practice_id = (int)$practice->id;
//                        $ccda->save();
//
//                        ProcessCcda::withChain([
//                            new CheckCcdaEnrollmentEligibility($ccda->id, $practice, $batch),
//                        ])->dispatch($ccda->id)
//                                   ->onQueue('ccda-processor');
//
//                        $localDisk->delete($path);
//                    }
//
//                    $localDisk->deleteDir("zip/$dir");
//
//                    return $file;
//                }
//
//                return false;
//            })
//            ->filter()
//            ->values();
//
//        if ($zipFiles->isNotEmpty()) {
//            return 'done';
//        }

        $ccds = $contents->where('type', '=', 'file')
                         ->whereIn('mimetype', [
                             'text/xml',
                             'application/xml',
                         ])
                         ->take(10);

        if ($ccds->isEmpty()) {
            return false;
        }

        return $ccds->map(function ($file) use (
            $cloudDisk,
            $practice,
            $dir,
            $filterLastEncounter,
            $filterInsurance,
            $filterProblems,
            $processedDir,
            $batch
        ) {
            $driveFilePath = $file['path'];

            $rawData = $cloudDisk->get($driveFilePath);

            if (str_contains($file['filename'], ['processed'])) {
                $cloudDisk->move($file['path'], "{$processedDir['path']}/{$file['filename']}");

                return $file;
            }

            $ccda = Ccda::create([
                'batch_id' => $batch->id,
                'source'   => Ccda::GOOGLE_DRIVE . "_$dir",
                'xml'      => $rawData,
                'status'   => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                'imported' => false,
            ]);

            //for some reason it doesn't save practice_id when using Ccda::create([])
            $ccda->practice_id = (int)$practice->id;
            $ccda->save();

            ProcessCcda::withChain([
                (new CheckCcdaEnrollmentEligibility($ccda->id, $practice, $batch))->onQueue('ccda-processor'),
            ])->dispatch($ccda->id)
                       ->onQueue('ccda-processor');

            $cloudDisk->move($file['path'],
                "{$processedDir['path']}/ccdaId=$ccda->id::processed={$file['filename']}");

            return $file;
        })
                    ->filter()
                    ->values();
    }

    public function queueFromGoogleDrive(EligibilityBatch $batch)
    {
        ProcessEligibilityFromGoogleDrive::dispatch($batch);
    }

    public function handleAlreadyDownloadedZip(
        $dir,
        $practiceName,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        $cloudDisk = Storage::cloud();

        $practice  = Practice::whereName($practiceName)->firstOrFail();
        $recursive = false; // Get subdirectories also?
        $contents  = collect($cloudDisk->listContents($dir, $recursive));

        $processedDir = $contents->where('type', '=', 'dir')
                                 ->where('filename', '=', 'processed')
                                 ->first();

        if ( ! $processedDir) {
            $cloudDisk->makeDirectory("$dir/processed");

            $processedDir = collect($cloudDisk->listContents($dir, $recursive))
                ->where('type', '=', 'dir')
                ->where('filename', '=', 'processed')
                ->first();
        }

        $zipFiles = $contents
            ->where('type', '=', 'file')
            ->where('mimetype', '=', 'application/zip')
            ->map(function ($file) use (
                $cloudDisk,
                $practice,
                $dir,
                $filterLastEncounter,
                $filterInsurance,
                $filterProblems,
                $processedDir
            ) {
                $localDisk = Storage::disk('local');

                $unzipDir = "zip/$dir/unzipped";

                $cloudFilePath = $file['path'];
                $cloudFileName = $file['filename'];
                $cloudDirName  = $file['dirname'];

                foreach ($localDisk->allFiles($unzipDir) as $path) {
                    if (str_contains($path, 'xml')) {
                        $now       = Carbon::now()->toAtomString();
                        $randomStr = str_random();

                        $put  = $cloudDisk->put("{$processedDir['path']}/$randomStr-$now",
                            fopen($localDisk->path($path), 'r+'));
                        $ccda = Ccda::create([
                            'source'      => Ccda::GOOGLE_DRIVE . "_$dir",
                            'xml'         => stream_get_contents(fopen($localDisk->path($path), 'r+')),
                            'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                            'imported'    => false,
                            'practice_id' => (int)$practice->id,
                        ]);

                        //for some reason it doesn't save practice_id when using Ccda::create([])
                        $ccda->practice_id = (int)$practice->id;
                        $ccda->save();

                        ProcessCcda::withChain([
                            (new CheckCcdaEnrollmentEligibility($ccda->id, $practice, (bool)$filterLastEncounter,
                                (bool)$filterInsurance, (bool)$filterProblems))->onQueue('ccda-processor'),
                        ])->dispatch($ccda->id)
                                   ->onQueue('ccda-processor');
                    } else {
                        $pathWithUnderscores = str_replace('/', '_', $path);
                        $put                 = $cloudDisk->put("{$processedDir['path']}/$pathWithUnderscores",
                            fopen($localDisk->path($path), 'r+'));
                    }

                    $localDisk->delete($path);
                }
            });

        return true;
    }

    /**
     * @param $dir
     * @param int $practiceId
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createGoogleDriveCcdsBatch(
        $dir,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->createBatch(EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS, $practiceId, [
            'dir'                 => $dir,
            'filterLastEncounter' => (boolean)$filterLastEncounter,
            'filterInsurance'     => (boolean)$filterInsurance,
            'filterProblems'      => (boolean)$filterProblems,
        ]);
    }

    public function createSingleCSVBatch(
        $patientList,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->createBatch(EligibilityBatch::TYPE_ONE_CSV, $practiceId, [
            'patientList'         => $patientList,
            'filterLastEncounter' => (boolean)$filterLastEncounter,
            'filterInsurance'     => (boolean)$filterInsurance,
            'filterProblems'      => (boolean)$filterProblems,
        ]);
    }

    /**
     * @param $type
     *
     * @param int $practiceId
     * @param array $options
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createBatch($type, int $practiceId, $options = [])
    {
        return EligibilityBatch::create([
            'type'        => $type,
            'practice_id' => $practiceId,
            'status'      => EligibilityBatch::STATUSES['not_started'],
            'options'     => $options,
        ]);
    }

    /**
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createPhoenixHeartBatch()
    {
        return $this->createBatch(EligibilityBatch::TYPE_PHX_DB_TABLES,
            Practice::whereName('phoenix-heart')->firstOrFail()->id, [
                'filterLastEncounter' => false,
                'filterInsurance'     => true,
                'filterProblems'      => true,
            ]);
    }

    /**
     * @param EligibilityBatch $batch
     *
     * @throws \Exception
     */
    public function processCsvForEligibility(EligibilityBatch $batch)
    {
        if ($batch->type != EligibilityBatch::TYPE_ONE_CSV) {
            throw new \Exception('$batch is not of type `' . EligibilityBatch::TYPE_ONE_CSV . '`.`');
        }

        $collection = collect($batch->options['patientList']);

        if ($collection->isEmpty()) {
            return false;
        }

        $patientList = [];

        for ($i = 1; $i <= 2000; $i++) {
            $patient = $collection->shift();

            if ( ! is_array($patient)) {
                continue;
            }

            $patientList[] = $patient;

            $patient = $this->transformCsvRow($patient);

            $hash = $batch->practice->name . $patient['first_name'] . $patient['last_name'] . $patient['mrn'] . $patient['city'] . $patient['state'] . $patient['zip'];

            $job = EligibilityJob::whereHash($hash)->first();

            if ( ! $job) {
                $job = EligibilityJob::create([
                    'batch_id' => $batch->id,
                    'hash'     => $hash,
                    'data'     => $patient,
                ]);
            }

            $patient['eligibility_job_id'] = $job->id;

            if ($job->status == 0) {
                ProcessSinglePatientEligibility::dispatch(collect([$patient]), $job, $batch, $batch->practice);
            }
        }

        $options                = $batch->options;
        $options['patientList'] = $collection->toArray();
        $batch->options         = $options;
        $batch->save();

        return $patientList;
    }

    private function transformCsvRow($patient)
    {
        if (count(preg_grep('/^problem_[\d]*/', array_keys($patient))) > 0) {
            $problems = (new NumberedProblemFields())->handle($patient);

            $patient['problems_string'] = json_encode([
                'Problems' => $problems,
            ]);
        }

        if (count(preg_grep('/^medication_[\d]*/', array_keys($patient))) > 0) {
            $medications = (new NumberedMedicationFields())->handle($patient);

            $patient['medications_string'] = json_encode([
                'Medications' => $medications,
            ]);
        }

        if (count(preg_grep('/^allergy_[\d]*/', array_keys($patient))) > 0) {
            $allergies = (new NumberedAllergyFields())->handle($patient);

            $patient['allergies_string'] = json_encode([
                'Allergies' => $allergies,
            ]);
        }

        return $patient;
    }
}