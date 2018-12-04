<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MapSnomedToCpmProblems extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Picks problems from SnomedToICD10Map that satisfy the ranges specified in CPMProblems and puts them in SnomedToCPMICDMap, so that our snomed problems parser will never hit an ICD10 code that we don not support.';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'map:snomedtocpm';

    /**
     * Create a new command instance.
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
//        $cpmProblems = CpmProblem::all();
//        SnomedToCpmIcdMap::truncate();
//        foreach ($cpmProblems as $cpmProblem) {
//            $maps = SnomedToICD10Map::whereBetween('icd_10_code', [
//                $cpmProblem->icd10from,
//                $cpmProblem->icd10to,
//            ])->get()->toArray();
//            $saved = SnomedToCpmIcdMap::insert($maps);
//
//            if ($saved) {
//                continue;
//            }
//
//            //or else add it to the report
//            $failed[] = [
//                'problem' => $cpmProblem,
//                'saved'   => $saved,
//            ];
//        }
//
//        if (isset($failed)) {
//            foreach ($failed as $problem) {
//                $this->error($problem['problem']->name . ' failed.');
//            }
//        }

//        $this->info('Map generated Successfully');

        $this->alert('This module was commented out when we manually added new Cpm Problems.');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
        ];
    }
}
