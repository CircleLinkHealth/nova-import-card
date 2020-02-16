<?php

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Console\Command;

class FixAddLastEncounterToPcm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:FixAddLastEncounterToPcm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FixAddLastEncounterToPcm';

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
        EligibilityJob::whereJsonLength(
            'data->chargeable_services_codes_and_problems->G2065',
            '>',
            0
        )->with('targetPatient.ccda')->chunkById(200, function ($eJs) {
            foreach ($eJs as $e) {
                $encounters = collect($e->targetPatient->ccda->blueButtonJson()->encounters);
            
                $lastEncounter = $encounters->sortByDesc(function ($el) {
                    return $el->date;
                })->first();
            
                if (is_object($lastEncounter) && property_exists($lastEncounter, 'date')) {
                    $v = \Validator::make(['date' => $lastEncounter->date], ['date' => 'required|date']);
                
                    if ($v->passes()) {
                        $data = $e->data;
                        $data['last_encounter'] = $lastEncounter->date;
                        $e->data = $data;
                        $e->save();
                    }
                }
            }
        });
    }
}
