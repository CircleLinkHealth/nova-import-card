<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FixBatch235 extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make some changes to batch with id 235';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:batch235';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Ccda::with('targetPatient.eligibilityJob.enrollee')->whereBatchId(235)->chunkById(100, function (Collection $ccdas) {
            $ccdas->each(function (Ccda $ccd) {
                $ccd->json = null;

                $json = $ccd->bluebuttonJson();

                $enrollee = $ccd->targetPatient->eligibilityJob->enrollee;

                if ( ! $enrollee) {
                    return;
                }

                $encounters = collect($json->encounters);

                $lastEncounter = $encounters->sortByDesc(function ($el) {
                    return $el->date;
                })->first();

                if (property_exists($lastEncounter, 'date')) {
                    $v = \Validator::make(['date' => $lastEncounter->date], ['date' => 'required|date']);

                    if ($v->passes()) {
                        $enrollee->last_encounter = Carbon::parse($lastEncounter->date);
                        $enrollee->save();
                    }
                }
            });
        });
    }
}
