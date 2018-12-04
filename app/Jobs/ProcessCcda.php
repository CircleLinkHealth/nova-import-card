<?php

namespace App\Jobs;

use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCcda implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $ccda;

    /**
     * Create a new job instance.
     *
     * @param $ccda
     */
    public function __construct($ccda)
    {
        if (is_a($ccda, Ccda::class)) {
            $ccda = $ccda->id;
        }

        $this->ccda = Ccda::find($ccda);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ccda = $this->ccda;

        $json = $ccda->bluebuttonJson();

        if (! $json) {
            throw new \Exception("No response from ccd parser.");
        }

        $ccda->mrn = $json->demographics->mrn_number;

        if (array_key_exists(0, $json->document->documentation_of)) {
            $provider                      = (new CcdToLogTranformer())->provider($json->document->documentation_of[0]);
            $ccda->referring_provider_name = "{$provider['first_name']} {$provider['last_name']}";
        }

        $ccda->date = Carbon::parse($json->document->date)->toDateTimeString();

        $ccda->save();

        if (!$ccda->mrn) {
            $ccda->mrn = "clh#{$ccda->id}";
            $ccda->save();
        }

        $this->handleDuplicateCcdas($ccda);
    }

    public function handleDuplicateCcdas(Ccda $ccda)
    {
        $duplicates = Ccda::withTrashed()
                          ->where([
                              ['mrn', '=', $ccda->mrn],
                              ['practice_id', '=', $ccda->practice_id],
                          ])
                          ->get(['id', 'date'])
                          ->sortByDesc(function ($ccda) {
                              return $ccda->date;
                          })->values();

        $keep = $duplicates->first();

        foreach ($duplicates as $dup) {
            if ($dup->status && $dup->status != Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY) {
                $status = $dup->status;
            }

            if ($dup->id == $keep->id) {
                continue;
            }

            $dup->delete();
        }

        $keep->status = $status ?? Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY;
        $keep->save();
    }
}
