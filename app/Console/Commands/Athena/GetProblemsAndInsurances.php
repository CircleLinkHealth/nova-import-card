<?php

namespace App\Console\Commands\Athena;

use App\Services\AthenaAPI\Service;
use App\TargetPatient;
use Illuminate\Console\Command;

class GetProblemsAndInsurances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:getProblemsAndInsurances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves problems and insurances of a given patient from the Athena API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Service $athenaApi)
    {
        parent::__construct();

        $this->service = $athenaApi;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //gets the patient info ->patientId, practice id, department id, targetPatient table to process
        $patients = TargetPatient::where('status', '=', 'to_process')->get();



        //makes the calls
        foreach ($patients as $patient){

            $patientInfo = $this->service->getPatientProblemsAndInsurances($patient->ehr_patient_id, $patient->ehr_practice_id, $patient->ehr_department_id);

            return $patientInfo;



            //call job to determine eligibility
            //determine($patientProblems, $patientInsurances);

        }


    }
}
