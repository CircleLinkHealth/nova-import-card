<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/14/18
 * Time: 3:58 PM
 */

namespace App\Services\CCD;

use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\ProcessCcda;
use App\Jobs\ProcessEligibilityFromGoogleDrive;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use ZanySoft\Zip\Zip;


class ProcessEligibilityService
{
    public function fromGoogleDrive($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems)
    {
        $cloudDisk = Storage::cloud();

        $practice  = Practice::whereName($practiceName)->firstOrFail();
        $recursive = false; // Get subdirectories also?
        $contents  = collect($cloudDisk->listContents($dir, $recursive));

        $zipFiles = $contents
            ->where('type', '=', 'file')
            ->where('mimetype', '=', 'application/zip')
            ->map(function ($file) use (
                $cloudDisk,
                $practice,
                $dir,
                $filterLastEncounter,
                $filterInsurance,
                $filterProblems
            ) {
                $cloudFilePath = $file['path'];
                $cloudFileName = $file['filename'];
                $cloudDirName  = $file['dirname'];

                $localDisk = Storage::disk('local');

                $stream = $cloudDisk->getDriver()
                                    ->readStream($cloudFilePath);

                $targetFile = "zip/$dir/$cloudFileName";

                $localDisk->put($targetFile, stream_get_contents($stream));

                $isValid = Zip::check($localDisk->path($targetFile));

                if ($isValid) {
                    $unzipDir = "zip/$dir/unzipped";

                    $localDisk->makeDirectory($unzipDir);

                    $zip = Zip::open($localDisk->path($targetFile));
                    $zip->extract($localDisk->path($unzipDir));
                    $zip->close();

                    foreach ($localDisk->allFiles($unzipDir) as $path) {
                        if (Zip::check($localDisk->path($path))) {
                            $zip = Zip::open($localDisk->path($path));
                            $zip->extract($localDisk->path($unzipDir));
                            $zip->close();
                        }

                        $now       = Carbon::now()->toAtomString();
                        $randomStr = str_random();
                        $put       = $cloudDisk->put($cloudDirName . "/$randomStr-$now",
                            fopen($localDisk->path($path), 'r+'));

                        $ccda = Ccda::create([
                            'source'   => Ccda::GOOGLE_DRIVE."_$dir",
                            'xml'      => stream_get_contents(fopen($localDisk->path($path), 'r+')),
                            'status'   => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                            'imported' => false,
                            'practice_id' => (int)$practice->id,
                        ]);

                        //for some reason it doesn't save practice_id when using Ccda::create([])
                        $ccda->practice_id = (int)$practice->id;
                        $ccda->save();

                        ProcessCcda::withChain([
                            new CheckCcdaEnrollmentEligibility($ccda->id, $practice, (bool)$filterLastEncounter,
                                (bool)$filterInsurance, (bool)$filterProblems),
                        ])->dispatch($ccda->id);

                        $localDisk->delete($path);
                    }

                    $localDisk->deleteDir("zip/$dir");

                    return $file;
                }

                return false;
            })
            ->filter()
            ->values();

        if ($zipFiles->isNotEmpty()) {
            return 'done';
        }

        return $contents->where('type', '=', 'file')
                        ->whereIn('mimetype', [
                            'text/xml',
                            'application/xml',
                        ])
                        ->map(function ($file) use (
                            $cloudDisk,
                            $practice,
                            $dir,
                            $filterLastEncounter,
                            $filterInsurance,
                            $filterProblems
                        ) {
                            $driveFilePath = $file['path'];

                            $rawData = $cloudDisk->get($driveFilePath);

                            $ccda = Ccda::create([
                                'source'   => Ccda::GOOGLE_DRIVE."_$dir",
                                'xml'      => $rawData,
                                'status'   => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                                'imported' => false,
                            ]);

                            //for some reason it doesn't save practice_id when using Ccda::create([])
                            $ccda->practice_id = (int)$practice->id;
                            $ccda->save();

                            ProcessCcda::withChain([
                                new CheckCcdaEnrollmentEligibility($ccda->id, $practice, (bool)$filterLastEncounter,
                                    (bool)$filterInsurance, (bool)$filterProblems),
                            ])->dispatch($ccda->id);

                            return $file;
                        })
                        ->filter()
                        ->values();
    }

    public function queueFromGoogleDrive($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems) {
        ProcessEligibilityFromGoogleDrive::dispatch($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems);
    }

    public function handleAlreadyDownloadedZip($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems) {
        $cloudDisk = Storage::cloud();

        $practice  = Practice::whereName($practiceName)->firstOrFail();
        $recursive = false; // Get subdirectories also?
        $contents  = collect($cloudDisk->listContents($dir, $recursive));

        $zipFiles = $contents
            ->where('type', '=', 'file')
            ->where('mimetype', '=', 'application/zip')
            ->map(function ($file) use (
                $cloudDisk,
                $practice,
                $dir,
                $filterLastEncounter,
                $filterInsurance,
                $filterProblems
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
                            new CheckCcdaEnrollmentEligibility($ccda->id, $practice, (bool)$filterLastEncounter,
                                (bool)$filterInsurance, (bool)$filterProblems),
                        ])->dispatch($ccda->id);
                    }

                    $localDisk->delete($path);
                }
            });

        return true;
    }
}