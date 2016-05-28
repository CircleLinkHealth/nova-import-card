<?php namespace App\Console\Commands;

use App\Models\CPM\CpmProblem;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\CLH\CCD\Importer\SnomedToICD10Map;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MapSnomedToCpmProblems extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'map:snomedtocpm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Picks problems from SnomedToICD10Map that satisfy the ranges specified in CPMProblems and puts them in SnomedToCPMICDMap, so that our snomed problems parser will never hit an ICD10 code that we don not support.';

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
    public function fire()
    {
        $cpmProblems = CpmProblem::all();
        SnomedToCpmIcdMap::truncate();
        foreach ( $cpmProblems as $cpmProblem ) {
            $maps = SnomedToICD10Map::whereBetween( 'icd_10_code', [$cpmProblem->icd10from, $cpmProblem->icd10to] )->get()->toArray();
            $saved = SnomedToCpmIcdMap::insert( $maps );

            if ( $saved ) continue;

            //or else add it to the report
            $failed[] = [
                'problem' => $cpmProblem,
                'saved' => $saved
            ];
        }

        if ( isset($failed) ) {
            foreach ( $failed as $problem ) {
                $this->error( $problem[ 'problem' ]->name . ' failed.');
            }
        }

        $this->info( 'Map generated Successfully' );
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
