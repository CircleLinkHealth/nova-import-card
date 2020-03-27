<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Http\Controllers;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use CircleLinkHealth\Eligibility\Adapters\JsonMedicalRecordEligibilityJobToCsvAdapter;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EligibilityBatchController extends Controller
{
    /**
     * @var ProcessEligibilityService
     */
    private $processEligibilityService;
    
    public function __construct(ProcessEligibilityService $processEligibilityService)
    {
        $this->processEligibilityService = $processEligibilityService;
    }
    
    public function allJobsCount()
    {
        $statuses = array_flip(EligibilityJob::STATUSES);
        
        return EligibilityJob::selectRaw('count(*) as total, status')->groupBy('status')->get()->mapWithKeys(
            function (
                $result
            ) use ($statuses) {
                return [$statuses[$result['status']] => $result['total']];
            }
        );
    }
    
    public function csvCreate()
    {
        return view('eligibilityBatch.methods.single-csv');
    }
    
    public function downloadAllPatientsCsv(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);
        
        $practice = Practice::findOrFail($batch->practice_id);
        $fileName = "{$practice->display_name} patient list from batch {$batch->id} exported at".Carbon::now(
            )->toAtomString();
        
        $response = new StreamedResponse(
            function () use ($batch) {
                // Open output stream
                $handle = fopen('php://output', 'w');
                
                $firstIteration = true;
                
                $batch->eligibilityJobs()
                      ->chunk(
                          500,
                          function ($jobs) use ($handle, &$firstIteration) {
                              foreach ($jobs as $job) {
                                  $data = $job->data;
                            
                                  if ($firstIteration) {
                                      // Add CSV headers
                                      fputcsv($handle, array_keys($data));
                                
                                      $firstIteration = false;
                                  }
                            
                                  foreach ($data as $key => $value) {
                                      if (is_array($value)) {
                                          $data[$key] = json_encode($value);
                                      }
                                  }
                            
                                  // Add a new row with data
                                  fputcsv($handle, $data);
                              }
                          }
                      );
                
                // Close the output stream
                fclose($handle);
            }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]
        );
        
        return $response;
    }
    
    public function downloadAthenaApiInsuranceCopaysCsv(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);
        
        $fileName = 'batch_id_'.$batch->id.'_athena_insurance_copays'.Carbon::now()->toAtomString();
        
        $response = new StreamedResponse(
            function () use ($batch) {
                // Open output stream
                $handle = fopen('php://output', 'w');
                
                $firstIteration = true;
                $headers        = [];
                
                InsuranceLog::whereMedicalRecordType(Ccda::class)
                            ->distinct()
                            ->whereNotNull('raw')
                            ->join(
                                'target_patients',
                                'target_patients.ccda_id',
                                '=',
                                'insurance_logs.medical_record_id'
                            )
                            ->where('target_patients.batch_id', $batch->id)
                            ->chunkById(
                                500,
                                function (Collection $joinResults) use ($handle, &$firstIteration, &$headers) {
                                    $joinResults->each(
                                        function ($row) use ($handle, &$firstIteration, &$headers) {
                                            $rawApiResponse = $row->raw;
                                
                                            if (array_key_exists('copays', $rawApiResponse)) {
                                                foreach ($rawApiResponse['copays'] as $copay) {
                                                    $data = [
                                                        'key_ccda_id'            => $row->ccda_id ?? 'N/A',
                                                        'key_eligibility_job_id' => $row->eligibility_job_id ?? 'N/A',
                                                        'key_enrollee_id'        => $row->enrollee_id ?? 'N/A',
                                                        'key_ehr_patient_id'     => $row->ehr_patient_id ?? 'N/A',
                                                        'key_insurance_log_id'   => $row->id ?? 'N/A',
                                            
                                                        'copayamount' => $copay['copayamount'] ?? 'N/A',
                                                        'copaytype'   => $copay['copaytype'] ?? 'N/A',
                                                    ];
                                        
                                                    if ($firstIteration) {
                                                        $headers = array_keys($data);
                                                        // Add CSV headers
                                                        fputcsv($handle, $headers);
                                            
                                                        $firstIteration = false;
                                                    }
                                                    // Add a new row with data
                                                    fputcsv($handle, $data);
                                                }
                                            }
                                        }
                                    );
                                },
                                'insurance_logs.id'
                            );
                
                // Close the output stream
                fclose($handle);
            }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]
        );
        
        return $response;
    }
    
    public function downloadAthenaApiInsuranceInfoCsv(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);
        
        $fileName = 'batch_id_'.$batch->id.'_athena_insurance_info'.Carbon::now()->toAtomString();
        
        $response = new StreamedResponse(
            function () use ($batch) {
//            // Open output stream
                $handle = fopen('php://output', 'w');
                
                $firstIteration = true;
                $headers        = [];
                
                InsuranceLog::whereMedicalRecordType(Ccda::class)
                            ->distinct()
                            ->whereNotNull('raw')
                            ->join(
                                'target_patients',
                                'target_patients.ccda_id',
                                '=',
                                'insurance_logs.medical_record_id'
                            )
                            ->where('target_patients.batch_id', $batch->id)
                            ->chunkById(
                                500,
                                function (Collection $joinResults) use ($handle, &$firstIteration, &$headers) {
                                    $joinResults->each(
                                        function ($row) use ($handle, &$firstIteration, &$headers) {
                                            $rawApiResponse = $row->raw;
                                
                                            $data = [
                                                'key_ccda_id'            => $row->ccda_id ?? 'N/A',
                                                'key_eligibility_job_id' => $row->eligibility_job_id ?? 'N/A',
                                                'key_enrollee_id'        => $row->enrollee_id ?? 'N/A',
                                                'key_ehr_patient_id'     => $row->ehr_patient_id ?? 'N/A',
                                    
                                                'insurancepolicyholdercountrycode'    => $rawApiResponse['insurancepolicyholdercountrycode'] ?? 'N/A',
                                                'sequencenumber'                      => $rawApiResponse['sequencenumber'] ?? 'N/A',
                                                'insurancepolicyholderlastname'       => $rawApiResponse['insurancepolicyholderlastname'] ?? 'N/A',
                                                'insuredentitytypeid'                 => $rawApiResponse['insuredentitytypeid'] ?? 'N/A',
                                                'insuranceidnumber'                   => $rawApiResponse['insuranceidnumber'] ?? 'N/A',
                                                'insurancepolicyholderstate'          => $rawApiResponse['insurancepolicyholderstate'] ?? 'N/A',
                                                'insurancepolicyholderzip'            => $rawApiResponse['insurancepolicyholderzip'] ?? 'N/A',
                                                'insurancepolicyholderdob'            => $rawApiResponse['insurancepolicyholderdob'] ?? 'N/A',
                                                'issuedate'                           => $rawApiResponse['issuedate'] ?? 'N/A',
                                                'relationshiptoinsured'               => $rawApiResponse['relationshiptoinsured'] ?? 'N/A',
                                                'eligibilitystatus'                   => $rawApiResponse['eligibilitystatus'] ?? 'N/A',
                                                'policynumber'                        => $rawApiResponse['policynumber'] ?? 'N/A',
                                                'insurancepolicyholderaddress1'       => $rawApiResponse['insurancepolicyholderaddress1'] ?? 'N/A',
                                                'insurancepackageaddress1'            => $rawApiResponse['insurancepackageaddress1'] ?? 'N/A',
                                                'insurancepolicyholdersex'            => $rawApiResponse['insurancepolicyholdersex'] ?? 'N/A',
                                                'eligibilityreason'                   => $rawApiResponse['eligibilityreason'] ?? 'N/A',
                                                'ircname'                             => $rawApiResponse['ircname'] ?? 'N/A',
                                                'insuranceplanname'                   => $rawApiResponse['insuranceplanname'] ?? 'N/A',
                                                'insurancetype'                       => $rawApiResponse['insurancetype'] ?? 'N/A',
                                                'insurancephone'                      => $rawApiResponse['insurancephone'] ?? 'N/A',
                                                'insurancepackagestate'               => $rawApiResponse['insurancepackagestate'] ?? 'N/A',
                                                'insurancepackagecity'                => $rawApiResponse['insurancepackagecity'] ?? 'N/A',
                                                'relationshiptoinsuredid'             => $rawApiResponse['relationshiptoinsuredid'] ?? 'N/A',
                                                'insuranceid'                         => $rawApiResponse['insuranceid'] ?? 'N/A',
                                                'insurancepolicyholder'               => $rawApiResponse['insurancepolicyholder'] ?? 'N/A',
                                                'eligibilitylastchecked'              => $rawApiResponse['eligibilitylastchecked'] ?? 'N/A',
                                                'insurancepolicyholdermiddlename'     => $rawApiResponse['insurancepolicyholdermiddlename'] ?? 'N/A',
                                                'insurancepolicyholderfirstname'      => $rawApiResponse['insurancepolicyholderfirstname'] ?? 'N/A',
                                                'insurancepackageid'                  => $rawApiResponse['insurancepackageid'] ?? 'N/A',
                                                'insurancepolicyholdercountryiso3166' => $rawApiResponse['insurancepolicyholdercountryiso3166'] ?? 'N/A',
                                                'insuranceplandisplayname'            => $rawApiResponse['insuranceplandisplayname'] ?? 'N/A',
                                                'eligibilitymessage'                  => $rawApiResponse['eligibilitymessage'] ?? 'N/A',
                                                'insurancepolicyholdercity'           => $rawApiResponse['insurancepolicyholdercity'] ?? 'N/A',
                                                'insurancepackagezip'                 => $rawApiResponse['insurancepackagezip'] ?? 'N/A',
                                            ];
                                
                                            if ($firstIteration) {
                                                $headers = array_keys($data);
                                                // Add CSV headers
                                                fputcsv($handle, $headers);
                                    
                                                $firstIteration = false;
                                            }
                                            // Add a new row with data
                                            fputcsv($handle, $data);
                                        }
                                    );
                                },
                                'insurance_logs.id'
                            );
                
                // Close the output stream
                fclose($handle);
            }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]
        );
        
        return $response;
    }
    
    public function downloadBatchLogCsv(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);
        
        $fileName = 'batch_id_'.$batch->id.'_logs_'.Carbon::now()->toAtomString();
        
        $response = new StreamedResponse(
            function () use ($batch) {
                // Open output stream
                $handle = fopen('php://output', 'w');
                
                $firstIteration = true;
                
                $cpmProblemsMap = CpmProblem::pluck('name', 'id');
                
                $batch->eligibilityJobs()
                      ->select(
                          [
                              'batch_id',
                              'hash',
                              'messages',
                              'outcome',
                              'reason',
                              'status',
                              'bhi_problem_id',
                              'ccm_problem_2_id',
                              'ccm_problem_1_id',
                              'tertiary_insurance',
                              'secondary_insurance',
                              'primary_insurance',
                              'last_encounter',
                          ]
                      )
                      ->chunk(
                          500,
                          function ($jobs) use ($handle, &$firstIteration, $cpmProblemsMap) {
                              foreach ($jobs as $job) {
                                  $data = [
                                      'batch_id'            => $job->batch_id,
                                      'hash'                => $job->hash,
                                      'outcome'             => $job->outcome,
                                      'reason'              => $job->reason,
                                      'messages'            => json_encode($job->messages),
                                      'last_encounter'      => $job->last_encounter,
                                      'ccm_problem_1'       => $cpmProblemsMap[$job->ccm_problem_1_id] ?? '',
                                      'ccm_problem_2'       => $cpmProblemsMap[$job->ccm_problem_2_id] ?? '',
                                      'bhi_problem'         => $cpmProblemsMap[$job->bhi_problem_id] ?? '',
                                      'primary_insurance'   => $job->primary_insurance,
                                      'secondary_insurance' => $job->secondary_insurance,
                                      'tertiary_insurance'  => $job->tertiary_insurance,
                                      'processing_status'   => $job->getStatus(),
                                  ];
                            
                                  if ($firstIteration) {
                                      // Add CSV headers
                                      fputcsv($handle, array_keys($data));
                                
                                      $firstIteration = false;
                                  }
                                  // Add a new row with data
                                  fputcsv($handle, $data);
                              }
                          }
                      );
                
                // Close the output stream
                fclose($handle);
            }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]
        );
        
        return $response;
    }
    
    public function downloadCsvPatientList(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);
        
        $practice = Practice::findOrFail($batch->practice_id);
        $fileName = "{$practice->display_name}_batch_{$batch->id}_patient_list".Carbon::now()->toAtomString();
        
        $response = new StreamedResponse(
            function () use ($batch) {
                // Open output stream
                $handle = fopen('php://output', 'w');
                
                $firstIteration = true;
                
                $batch->eligibilityJobs()
                      ->chunk(
                          500,
                          function ($jobs) use ($handle, &$firstIteration) {
                              foreach ($jobs as $job) {
                                  $data = (new JsonMedicalRecordEligibilityJobToCsvAdapter($job))->toArray();
                            
                                  if ($firstIteration) {
                                      // Add CSV headers
                                      fputcsv($handle, array_keys($data));
                                
                                      $firstIteration = false;
                                  }
                                  // Add a new row with data
                                  fputcsv($handle, $data);
                              }
                          }
                      );
                
                // Close the output stream
                fclose($handle);
            }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]
        );
        
        return $response;
    }
    
    public function downloadEligibleCsv(EligibilityBatch $batch)
    {
        $practice = Practice::findOrFail($batch->practice_id);
        $fileName = $practice->display_name.'_'.Carbon::now()->toAtomString();
        
        $response = new StreamedResponse(
            function () use ($batch) {
                // Open output stream
                $handle = fopen('php://output', 'w');
                
                $firstIteration = true;
                
                Enrollee::select(
                    [
                        'enrollees.id as eligible_patient_id',
                        'enrollees.eligibility_job_id',
                        'enrollees.cpm_problem_1',
                        'enrollees.cpm_problem_2',
                        'enrollees.medical_record_type',
                        'enrollees.medical_record_id',
                        'enrollees.mrn',
                        'enrollees.first_name',
                        'enrollees.last_name',
                        'enrollees.address',
                        'enrollees.address_2',
                        'enrollees.city',
                        'enrollees.state',
                        'enrollees.zip',
                        'enrollees.primary_phone',
                        'enrollees.other_phone',
                        'enrollees.home_phone',
                        'enrollees.cell_phone',
                        'enrollees.email',
                        'enrollees.dob',
                        'enrollees.lang',
                        'enrollees.preferred_days',
                        'enrollees.preferred_window',
                        'enrollees.primary_insurance',
                        'enrollees.secondary_insurance',
                        'enrollees.tertiary_insurance',
                        'enrollees.last_encounter',
                        'enrollees.referring_provider_name',
                        'enrollees.problems',
                        'p1.name as ccm_condition_1',
                        'p2.name as ccm_condition_2',
                        'pbhi.name as bhi_condition',
                        'eligibility_jobs.data as jsonMedicalRecord',
                    ]
                )
                        ->leftJoin('cpm_problems as p1', 'p1.id', '=', 'enrollees.cpm_problem_1')
                        ->leftJoin('cpm_problems as p2', 'p2.id', '=', 'enrollees.cpm_problem_2')
                        ->leftJoin('eligibility_jobs', 'eligibility_jobs.id', '=', 'enrollees.eligibility_job_id')
                        ->leftJoin('cpm_problems as pbhi', 'pbhi.id', '=', 'eligibility_jobs.bhi_problem_id')
                        ->where('enrollees.batch_id', $batch->id)
                        ->whereNull('user_id')
                        ->chunk(
                            500,
                            function ($enrollees) use ($handle, &$firstIteration) {
                                foreach ($enrollees as $enrollee) {
                                    $data = [
                                        'eligible_patient_id'           => $enrollee->eligible_patient_id,
                                        'was_previously_found_eligible' => EligibilityJob::ELIGIBLE_ALSO_IN_PREVIOUS_BATCH == optional(
                                            $enrollee->eligibilityJob
                                        )->outcome
                                            ? 'Y'
                                            : 'N',
                                        'eligibility_job_id'            => $enrollee->eligibility_job_id,
                                        'cpm_problem_1'                 => $enrollee->cpm_problem_1,
                                        'cpm_problem_2'                 => $enrollee->cpm_problem_2,
                                        'medical_record_type'           => $enrollee->medical_record_type,
                                        'medical_record_id'             => $enrollee->medical_record_id,
                                        'mrn'                           => $enrollee->mrn,
                                        'first_name'                    => $enrollee->first_name,
                                        'last_name'                     => $enrollee->last_name,
                                        'location'                      => json_decode($enrollee->jsonMedicalRecord, true)['department_name'] ?? '',
                                        'address'                       => $enrollee->address,
                                        'address_2'                     => $enrollee->address_2,
                                        'city'                          => $enrollee->city,
                                        'state'                         => $enrollee->state,
                                        'zip'                           => $enrollee->zip,
                                        'primary_phone'                 => $enrollee->primary_phone,
                                        'other_phone'                   => $enrollee->other_phone,
                                        'home_phone'                    => $enrollee->home_phone,
                                        'cell_phone'                    => $enrollee->cell_phone,
                                        'email'                         => $enrollee->email,
                                        'dob'                           => $enrollee->dob,
                                        'lang'                          => $enrollee->lang,
                                        'preferred_days'                => $enrollee->preferred_days,
                                        'preferred_window'              => $enrollee->preferred_window,
                                        'primary_insurance'             => $enrollee->primary_insurance,
                                        'secondary_insurance'           => $enrollee->secondary_insurance,
                                        'tertiary_insurance'            => $enrollee->tertiary_insurance,
                                        'last_encounter'                => $enrollee->last_encounter,
                                        'referring_provider_name'       => $enrollee->referring_provider_name,
                                        'ccm_condition_1'               => $enrollee->ccm_condition_1,
                                        'ccm_condition_2'               => $enrollee->ccm_condition_2,
                                        'bhi_condition'                 => $enrollee->bhi_condition,
                                        'problems'                      => $enrollee->problems,
                                    ];
                            
                                    if ($firstIteration) {
                                        // Add CSV headers
                                        fputcsv($handle, array_keys($data));
                                
                                        $firstIteration = false;
                                    }
                                    // Add a new row with data
                                    fputcsv($handle, $data);
                                }
                            }
                        );
                
                // Close the output stream
                fclose($handle);
            }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]
        );
        
        return $response;
    }
    
    public function getCounts(EligibilityBatch $batch)
    {
        $unprocessed = 'N/A';
        $ineligible  = 'N/A';
        $duplicates  = 'N/A';
        
        if (EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS == $batch->type) {
            $unprocessed = Ccda::whereBatchId($batch->id)->whereStatus(Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)->count(
            );
            $ineligible  = Ccda::whereBatchId($batch->id)->whereStatus(Ccda::INELIGIBLE)->count();
            $duplicates  = Ccda::onlyTrashed()->whereBatchId($batch->id)->count();
        }
        
        $eligible = Enrollee::whereBatchId($batch->id)->whereNull('user_id')->count();
        
        return $this->ok(
            [
                'unprocessed' => $unprocessed,
                'ineligible'  => $ineligible,
                'duplicates'  => $duplicates,
                'eligible'    => $eligible,
            ]
        );
    }
    
    public function getLastImportLog(EligibilityBatch $batch)
    {
        $arr = json_decode(\Cache::get("batch:{$batch->id}:last_consented_enrollee_import"), true);
        
        $fileName = 'batch_id_'.$batch->id.'_'.Carbon::now()->toAtomString().'.xls';
        
        return (new FromArray($fileName, (array) $arr))->download($fileName);
    }
    
    /**
     * Show the form to edit EligibilityBatch options for re-processing.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getReprocess(EligibilityBatch $batch)
    {
        if (in_array(
            $batch->type,
            [EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE, EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS]
        )) {
            return view('eligibilityBatch.methods.google-drive')
                ->with('batch', $batch)
                ->with('action', 'edit');
        }
        if (EligibilityBatch::TYPE_ONE_CSV == $batch->type) {
            return view('eligibilityBatch.methods.single-csv');
        }
    }
    
    public function googleDriveCreate()
    {
        return view('eligibilityBatch.methods.google-drive');
    }
    
    public function index()
    {
        $batches = EligibilityBatch::orderByDesc('updated_at')
                                   ->with('practice')
                                   ->take(100)
                                   ->get();
        
        return view(
            'eligibilityBatch.index',
            compact(['batches'])
        );
    }
    
    /**
     * Store updated EligibilityBatch options for re-processing.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postReprocess(Request $request, EligibilityBatch $batch)
    {
        $folder              = $request->input('dir');
        $fileName            = $request->input('file');
        $filterLastEncounter = (bool) $request->input('filterLastEncounter');
        $filterInsurance     = (bool) $request->input('filterInsurance');
        $filterProblems      = (bool) $request->input('filterProblems');
        $reprocessingMethod  = $request->input('reprocessingMethod');
        
        switch ($batch->type) {
            case EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE:
                $updatedBatch = $this->processEligibilityService->prepareClhMedicalRecordTemplateBatchForReprocessing(
                    $batch,
                    $folder,
                    $fileName,
                    $filterLastEncounter,
                    $filterInsurance,
                    $filterProblems,
                    $reprocessingMethod
                );
                break;
        }
        
        return redirect()->route('eligibility.batch.show', [$updatedBatch->id]);
    }
    
    public function show(EligibilityBatch $batch)
    {
        $batch->load('practice');
        
        $initiatorUser   = $batch->initiatorUser;
        $validationStats = $batch->getValidationStats();
        
        $stats = $batch->getOutcomes();
        
        $enrolleesExist = (bool) Enrollee::whereBatchId($batch->id)->whereNull('user_id')->exists();
        
        $athenaInsurancesExist = false;
        
        if (EligibilityBatch::ATHENA_API == $batch->type) {
            $athenaInsurancesExist = InsuranceLog::whereMedicalRecordType(Ccda::class)
                                                 ->whereNotNull('raw')
                                                 ->join(
                                                     'target_patients',
                                                     'target_patients.ccda_id',
                                                     '=',
                                                     'insurance_logs.medical_record_id'
                                                 )->exists();
        }
        
        return view(
            'eligibility::batch.show',
            compact(
                [
                    'batch',
                    'enrolleesExist',
                    'stats',
                    'initiatorUser',
                    'validationStats',
                    'athenaInsurancesExist',
                ]
            )
        );
    }
}
