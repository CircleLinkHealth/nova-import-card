<?php

namespace App\Console\Commands\Athena;

use App\Adapters\EligibilityCheck\AthenaAPIAdapter;
use App\Enrollee;
use App\Practice;
use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
use App\TargetPatient;
use Illuminate\Console\Command;

class DetermineTargetPatientEligibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:DetermineTargetPatientEligibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves problems and insurances of a given patient from the Athena API';

    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DetermineEnrollmentEligibility $athenaApi)
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
        $patients = TargetPatient::where('status', '=', 'to_process')
                                 ->get()
                                 ->map(function ($patient){
                                     $patientInfo = $this->service->getPatientProblemsAndInsurances($patient->ehr_patient_id, $patient->ehr_practice_id, $patient->ehr_department_id);

                                     $adapter = new AthenaAPIAdapter($patientInfo);
                                     $isEligible = $adapter->isEligible();


                                     if (! $isEligible){
                                         $patient->status = 'ineligible';
                                         $patient->save();
                                         return false;
                                     }

                                     $demos = $this->api->getDemographics();

                                     if ($demos['homephone'] or $demos['mobilephone']) {
                                         $patient->status = 'eligible';
                                     }else {
                                         $patient->status = 'error';
                                         $patient->description = 'Homephone or mobile phone must be provided';
                                     }

                                     $practice = Practice::where('external_id'. '=', $patient->ehr_practice_id )->first();

                                     return Enrollee::create([
                                         //required
                                         'first_name' => $demos['firstname'],
                                         'last_name' => $demos['lastname'],
                                         'home_phone' => $demos['homephone'],
                                         'cell_phone' => $demos['mobilephone'],
                                         'practice_id' => $practice->id,

                                         //notRequired
                                         'address' => $demos['address1'],
                                         'address_2' => $demos['address2'],
                                         'dob' => $demos['dob'],
                                         'state' => $demos['state'],
                                         'city' => $demos['city'],
                                         'zip' => $demos['zip'],

                                     ]);
                                 });
    }
}
