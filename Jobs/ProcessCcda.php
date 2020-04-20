<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Core\Exceptions\InvalidCcdaException;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCcda implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
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
     */
    public function handle()
    {
        /** @var \CircleLinkHealth\SharedModels\Entities\Ccda $ccda */
        $ccda = $this->ccda;

        try {
            $json = $ccda->bluebuttonJson();
        } catch (InvalidCcdaException $e) {
            Log::error($e->getMessage());

            return;
        }

        if ( ! $json) {
            throw new \Exception('No response from ccd parser.');
        }

        $ccda->mrn = $json->demographics->mrn_number;

        if (array_key_exists(0, $json->document->documentation_of)) {
            $provider                      = (new CcdToLogTranformer())->provider($json->document->documentation_of[0]);
            $ccda->referring_provider_name = "{$provider['first_name']} {$provider['last_name']}";
        }

        $ccda->date = Carbon::parse($json->document->date)->toDateTimeString();

        $ccda->save();

        if ( ! $ccda->mrn) {
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
            if ($dup->status && Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY != $dup->status) {
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
