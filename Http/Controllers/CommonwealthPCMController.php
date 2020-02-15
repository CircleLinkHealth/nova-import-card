<?php

namespace CircleLinkHealth\Eligibility\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommonwealthPCMController extends Controller
{
    public function downloadCsvList()
    {
        ini_set('max_execution_time', 600);
        ini_set('max_memory', '2000M');
        
        $fileName = "Commonwealth Pain PCM Eligible Patients created ".Carbon::now()->toAtomString();
        
        return new StreamedResponse(
            function () {
                // Open output stream
                $handle = fopen('php://output', 'w');
                
                $firstIteration = true;
                
                EligibilityJob::whereJsonLength(
                    'data->chargeable_services_codes_and_problems->G2065',
                    '>',
                    0
                )->whereHas(
                    'batch',
                    function ($q) {
                        $q->where('practice_id', 232);
                    }
                )->with('targetPatient')->chunkById(
                    100,
                    function (Collection $eJs) use (&$firstIteration, $handle) {
                        $eJs->each(
                            function ($eJ) use (&$firstIteration, $handle) {
                                $problems = PcmProblem::whereIn(
                                    'id',
                                    $eJ->data['chargeable_services_codes_and_problems']['G2065']
                                )->get();
                                
                                $data = [
                                    'pcm_problem_code'        => optional($problems->first())->code,
                                    'pcm_problem_code_type'   => optional($problems->first())->code_type,
                                    'pcm_problem_description' => optional($problems->first())->description,
                                    
                                    'eligibility_job_id'      => $eJ->id,
                                    'cpm_patient_id'          => $eJ->targetPatient->user_id,
                                    'athenahealth_id'         => $eJ->targetPatient->ehr_patient_id,
                                    'medical_record_type'     => $eJ->data['medical_record_type'] ?? '',
                                    'medical_record_id'       => $eJ->data['medical_record_id'] ?? '',
                                    'mrn'                     => $eJ->data['mrn_number'] ?? '',
                                    'first_name'              => $eJ->data['first_name'],
                                    'last_name'               => $eJ->data['last_name'],
                                    'address'                 => $eJ->data['street'] ?? '',
                                    'address_2'               => $eJ->data['street2'] ?? '',
                                    'city'                    => $eJ->data['city'],
                                    'state'                   => $eJ->data['state'],
                                    'zip'                     => $eJ->data['zip'],
                                    'primary_phone'           => $eJ->data['primary_phone'] ?? '',
                                    'other_phone'             => $eJ->data['other_phone'] ?? '',
                                    'home_phone'              => $eJ->data['home_phone'] ?? '',
                                    'cell_phone'              => $eJ->data['cell_phone'] ?? '',
                                    'email'                   => $eJ->data['email'] ?? '',
                                    'dob'                     => $eJ->data['dob'],
                                    'lang'                    => $eJ->data['language'] ?? '',
                                    'primary_insurance'       => $eJ->data['primary_insurance'],
                                    'secondary_insurance'     => $eJ->data['secondary_insurance'],
                                    'tertiary_insurance'      => $eJ->data['tertiary_insurance'],
                                    'last_encounter'          => $eJ->data['last_encounter'] ?? '',
                                    'referring_provider_name' => $eJ->data['referring_provider_name'],

                                    'all_pcm_problems'        => $problems->toJson(),
                                ];
                                
                                if ($firstIteration) {
                                    // Add CSV headers
                                    fputcsv($handle, array_keys($data));
                                    
                                    $firstIteration = false;
                                }
                                // Add a new row with data
                                fputcsv($handle, $data);
                            }
                        );
                    }
                );
                // Close the output stream
                fclose($handle);
            }, 200, [
                 'Content-Type'        => 'text/csv',
                 'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
             ]
        );
    }
}
