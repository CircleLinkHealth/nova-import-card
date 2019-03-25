<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands\Athena;

use App\Adapters\EligibilityCheck\AthenaAPIAdapter;
use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use CircleLinkHealth\Customer\Entities\Practice;
use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
use App\TargetPatient;
use Illuminate\Console\Command;

class DetermineTargetPatientEligibility extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves problems and insurances of a given patient from the Athena API';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:DetermineTargetPatientEligibility {batchId? : The Eligibility Batch Id}';

    private $service;

    /**
     * Create a new command instance.
     *
     * @param DetermineEnrollmentEligibility $athenaApi
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
        TargetPatient::where('status', '=', 'to_process')
            ->get()
            ->each(function ($patient) {
                $patientInfo = $this->service->getPatientProblemsAndInsurances(
                    $patient->ehr_patient_id,
                    $patient->ehr_practice_id,
                    $patient->ehr_department_id
                                     );

                $batch = EligibilityBatch::find($this->argument('batchId'));
                $adapter = new AthenaAPIAdapter(
                    $patientInfo,
                    new EligibilityJob(['batch_id' => $batch->id]),
                    $batch
                                     );
                $isEligible = $adapter->isEligible();

                $job = $adapter->getEligibilityJob();
                $patient->eligibility_job_id = $job->id;

                if ( ! $isEligible) {
                    $patient->status = 'ineligible';
                    $patient->save();

                    return false;
                }

                $demos = $this->service->getDemographics(
                    $patient->ehr_patient_id,
                    $patient->ehr_practice_id
                                     );

                try {
                    $demos = $demos[0];
                } catch (\Exception $e) {
                    throw new \Exception('Get Demographics api call failed.', 500, $e);
                }

                if ($demos['homephone'] or $demos['mobilephone']) {
                    $patient->status = 'eligible';
                } else {
                    $patient->status = 'error';
                    $patient->description = 'Homephone or mobile phone must be provided';
                }

                $practice = Practice::where(
                    'external_id',
                    '=',
                    $patient->ehr_practice_id
                                     )->first();

                if ( ! $practice) {
                    throw new \Exception(
                        "Practice with AthenaId {$patient->ehr_practice_id} was not found.",
                        500
                                         );
                }

                $insurances = $patientInfo->getInsurances();

                try {
                    $enrollee = Enrollee::create([
                        //required
                        'first_name'  => $demos['firstname'],
                        'last_name'   => $demos['lastname'],
                        'home_phone'  => $demos['homephone'],
                        'cell_phone'  => $demos['mobilephone'] ?? null,
                        'practice_id' => $practice->id,
                        'batch_id'    => $this->argument('batchId') ?? null,

                        'status' => Enrollee::TO_CALL,

                        //notRequired
                        'address'   => $demos['address1'] ?? null,
                        'address_2' => $demos['address2'] ?? null,
                        'dob'       => $demos['dob'],
                        'state'     => $demos['state'],
                        'city'      => $demos['city'] ?? null,
                        'zip'       => $demos['zip'] ?? null,

                        'primary_insurance' => array_key_exists(0, $insurances)
                            ? $insurances[0]['insurancetype'] ?? $insurances[0]['insuranceplanname']
                            : '',
                        'secondary_insurance' => array_key_exists(1, $insurances)
                            ? $insurances[1]['insurancetype'] ?? $insurances[1]['insuranceplanname']
                            : '',
                        'tertiary_insurance' => array_key_exists(2, $insurances)
                            ? $insurances[2]['insurancetype'] ?? $insurances[2]['insuranceplanname']
                            : '',

                        'cpm_problem_1' => $adapter->getEligiblePatientList()->first()->get('cpm_problem_1'),
                        'cpm_problem_2' => $adapter->getEligiblePatientList()->first()->get('cpm_problem_2'),
                    ]);

                    $patient->enrollee_id = $enrollee->id;
                } catch (\Exception $e) {
                    //check if this is a mysql exception for unique key constraint
                    if ($e instanceof \Illuminate\Database\QueryException) {
                        $errorCode = $e->errorInfo[1];
                        if (1062 == $errorCode) {
                            //do nothing
                                                 //we don't actually want to terminate the program if we detect duplicates
                                                 //we just don't wanna add the row again
                        }
                    }
                }

                $patient->save();

                return $enrollee ?? null;
            });
    }
}
