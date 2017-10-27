<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCcda implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public $ccda;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Ccda $ccda)
    {
        $this->ccda = Ccda::find($ccda->id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ccda = $this->ccda;

        $json = json_decode((new CCDImporterRepository())->toJson($ccda->xml));

        if ($json) {
            $ccda->mrn = $json->demographics->mrn_number;

            if (array_key_exists(0, $json->document->documentation_of)) {
                $provider = (new CcdToLogTranformer())->provider($json->document->documentation_of[0]);
                $ccda->referring_provider_name = "{$provider['first_name']} {$provider['last_name']}";
            }

            $ccda->date = Carbon::parse($json->document->date)->toDateTimeString();

            $ccda->save();
        }

        $this->handleDuplicateCcdas($ccda);
    }

    public function handleDuplicateCcdas(Ccda $ccda)
    {
        $duplicates = Ccda::where('mrn', '=', $ccda->mrn)
            ->get(['id', 'date'])
            ->sortByDesc(function ($ccda) {
                return $ccda->date;
            })->values();

        $keep = $duplicates->first();

        foreach ($duplicates as $dup) {
            if ($dup->id == $keep->id) {
                continue;
            }

            $dup->delete();
        }
    }
}
