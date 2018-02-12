<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CheckCcdaEnrollmentEligibility;
use App\Jobs\ProcessCcda;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use Illuminate\Support\Facades\Storage;

class ProcessEligibilityController extends Controller
{
    public function fromGoogleDrive($dir, $practiceName, $filterLastEncounter, $filterInsurance, $filterProblems) {
        $disk = Storage::cloud();

        $practice = Practice::whereName($practiceName)->first();
        $recursive = false; // Get subdirectories also?
        $contents = collect($disk->listContents($dir, $recursive));

        $processedDir = $contents->where('type', '=', 'dir')
                                 ->where('filename', '=', 'processed')
                                 ->first();

        if (!$processedDir) {
            $disk->makeDirectory("$dir/processed");

            $processedDir = collect($disk->listContents($dir, $recursive))
                ->where('type', '=', 'dir')
                ->where('filename', '=', 'processed')
                ->first();
        }

        return $contents->where('type', '=', 'file')
                        ->where('mimetype', '=', 'text/xml')
                        ->take(3000)
                        ->map(function ($file) use ($disk, $practice, $dir, $filterLastEncounter, $filterInsurance, $filterProblems, $processedDir){
                            $rawData = $disk->get($file['path']);

                            if (str_contains($file['filename'], ['processed'])) {
                                $disk->move($file['path'], "{$processedDir['path']}/{$file['filename']}");

                                return $file;
                            }

                            $ccda = Ccda::create([
                                'source'      => 'uploaded',
                                'xml'         => $rawData,
                                'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
                                'imported'    => false,
                            ]);

                            //for some reason it doesn't save practice_id when using Ccda::create([])
                            $ccda->practice_id = (int) $practice->id;
                            $ccda->save();

                            ProcessCcda::withChain([
                                new CheckCcdaEnrollmentEligibility($ccda->id, $practice, (bool) $filterLastEncounter,
                                    (bool) $filterInsurance, (bool) $filterProblems),
                            ])->dispatch($ccda->id);

                            $disk->move($file['path'], "{$processedDir['path']}/ccdaId=$ccda->id::processed={$file['filename']}");

                            return $file;
                        })
                        ->filter()
                        ->values();
    }
}
