<?php

namespace App\Http\Controllers;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use App\Http\Resources\EligibilityJobCSVRow;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Adapters\JsonMedicalRecordEligibilityJobToCsvAdapter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
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

    public function googleDriveCreate()
    {
        return view('eligibilityBatch.methods.google-drive');
    }

    public function csvCreate()
    {
        return view('eligibilityBatch.methods.single-csv');
    }

    public function index()
    {
        $batches = EligibilityBatch::orderByDesc('updated_at')
                                   ->with('practice')
                                   ->take(100)
                                   ->get();

        return view('eligibilityBatch.index',
            compact(['batches']));
    }

    /**
     * Show the form to edit EligibilityBatch options for re-processing
     *
     * @param EligibilityBatch $batch
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getReprocess(EligibilityBatch $batch)
    {
        if (in_array($batch->type,
            [EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE, EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS])) {
            return view('eligibilityBatch.methods.google-drive')
                ->with('batch', $batch)
                ->with('action', 'edit');
        } elseif ($batch->type == EligibilityBatch::TYPE_ONE_CSV) {
            return view('eligibilityBatch.methods.single-csv');
        }
    }

    /**
     * Store updated EligibilityBatch options for re-processing
     *
     * @param Request $request
     * @param EligibilityBatch $batch
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postReprocess(Request $request, EligibilityBatch $batch)
    {
        $folder              = $request->input('dir');
        $fileName            = $request->input('file');
        $filterLastEncounter = (boolean)$request->input('filterLastEncounter');
        $filterInsurance     = (boolean)$request->input('filterInsurance');
        $filterProblems      = (boolean)$request->input('filterProblems');
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
        $unprocessed = '';
        $ineligible  = '';
        $duplicates  = '';
        $eligible    = '';
        $stats       = '';

        $batch->load('practice');

        if ($batch->type == EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS) {
            $statuses = Ccda::select(['status', 'deleted_at'])
                            ->withTrashed()
                            ->whereBatchId($batch->id)
                            ->get();

            $unprocessed = $statuses->where('status', Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)->where('deleted_at',
                null)->count();
            $ineligible  = $statuses->where('status', Ccda::INELIGIBLE)->where('deleted_at', null)->count();
            $duplicates  = $statuses->where('deleted_at', '!=', null)->count();
            $eligible    = Enrollee::whereBatchId($batch->id)->whereNull('user_id')->count();
        } else {
            $stats = $batch->getOutcomes();
        }

        $enrolleesExist = ! ! Enrollee::whereBatchId($batch->id)->whereNull('user_id')->exists();


        return view('eligibilityBatch.show')
            ->with('batch', $batch)
            ->with('enrolleesExist', $enrolleesExist)
            ->with('stats', $stats)
            ->with('eligible', $eligible)
            ->with('unprocessed', $unprocessed)
            ->with('ineligible', $ineligible)
            ->with('duplicates', $duplicates);
    }

    public function getCounts(EligibilityBatch $batch)
    {
        $unprocessed = 'N/A';
        $ineligible  = 'N/A';
        $duplicates  = 'N/A';

        if ($batch->type == EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS) {
            $unprocessed = Ccda::whereBatchId($batch->id)->whereStatus(Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)->count();
            $ineligible  = Ccda::whereBatchId($batch->id)->whereStatus(Ccda::INELIGIBLE)->count();
            $duplicates  = Ccda::onlyTrashed()->whereBatchId($batch->id)->count();
        }

        $eligible = Enrollee::whereBatchId($batch->id)->whereNull('user_id')->count();

        return $this->ok([
            'unprocessed' => $unprocessed,
            'ineligible'  => $ineligible,
            'duplicates'  => $duplicates,
            'eligible'    => $eligible,
        ]);
    }

    public function allJobsCount()
    {
        $statuses = array_flip(EligibilityJob::STATUSES);

        return EligibilityJob::selectRaw('count(*) as total, status')->groupBy('status')->get()->mapWithKeys(function (
            $result
        ) use ($statuses) {
            return [$statuses[$result['status']] => $result['total']];
        });
    }

    public function downloadEligibleCsv(EligibilityBatch $batch)
    {
        $practice = Practice::findOrFail($batch->practice_id);
        $fileName = $practice->display_name . '_' . Carbon::now()->toAtomString();

        $response = new StreamedResponse(function () use ($batch) {
            // Open output stream
            $handle = fopen('php://output', 'w');

            $firstIteration = true;

            Enrollee::select([
                'enrollees.id as eligible_patient_id',
                'cpm_problem_1',
                'cpm_problem_2',
                'medical_record_type',
                'medical_record_id',
                'mrn',
                'first_name',
                'last_name',
                'address',
                'address_2',
                'city',
                'state',
                'zip',
                'primary_phone',
                'other_phone',
                'home_phone',
                'cell_phone',
                'email',
                'dob',
                'lang',
                'preferred_days',
                'preferred_window',
                'primary_insurance',
                'secondary_insurance',
                'tertiary_insurance',
                'last_encounter',
                'referring_provider_name',
                'problems',
                'p1.name as ccm_condition_1',
                'p2.name as ccm_condition_2',
            ])
                    ->join('cpm_problems as p1', 'p1.id', '=', 'enrollees.cpm_problem_1')
                    ->leftJoin('cpm_problems as p2', 'p2.id', '=', 'enrollees.cpm_problem_2')
                    ->whereBatchId($batch->id)
                    ->whereNull('user_id')
                    ->chunk(500, function ($enrollees) use ($handle, &$firstIteration) {
                        foreach ($enrollees as $enrollee) {
                            $data = $enrollee->toArray();

                            if ($firstIteration) {
                                // Add CSV headers
                                fputcsv($handle, array_keys($data));

                                $firstIteration = false;
                            }
                            // Add a new row with data
                            fputcsv($handle, $data);
                        }
                    });

            // Close the output stream
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
        ]);

        return $response;
    }

    public function downloadCsvPatientList(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);

        $practice = Practice::findOrFail($batch->practice_id);
        $fileName = "{$practice->display_name}_batch_{$batch->id}_patient_list" . Carbon::now()->toAtomString();

        $response = new StreamedResponse(function () use ($batch) {
            // Open output stream
            $handle = fopen('php://output', 'w');

            $firstIteration = true;

            $batch->eligibilityJobs()
                  ->chunk(500, function ($jobs) use ($handle, &$firstIteration) {
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
                  });

            // Close the output stream
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
        ]);

        return $response;
    }

    public function getLastImportLog(EligibilityBatch $batch)
    {
        $arr = json_decode(\Cache::get("batch:{$batch->id}:last_consented_enrollee_import"), true);

        $fileName = 'batch_id_' . $batch->id . '_' . Carbon::now()->toAtomString();

        return Excel::create($fileName, function ($excel) use ($arr) {
            $excel->sheet('Sheet', function ($sheet) use ($arr) {
                $sheet->fromArray($arr);
            });
        })->download('csv');
    }

    public function downloadBatchLogCsv(EligibilityBatch $batch)
    {
        ini_set('max_execution_time', 300);

        $fileName = 'batch_id_' . $batch->id . '_logs_' . Carbon::now()->toAtomString();

        $response = new StreamedResponse(function () use ($batch) {
            // Open output stream
            $handle = fopen('php://output', 'w');

            $firstIteration = true;

            $batch->eligibilityJobs()
                  ->select([
                      'batch_id',
                      'hash',
                      'messages',
                      'outcome',
                      'reason',
                      'status',
                  ])
                  ->chunk(500, function ($jobs) use ($handle, &$firstIteration) {
                      foreach ($jobs as $job) {
                          $data = $job->toArray();

                          $data['messages'] = json_encode($data['messages']);

                          if ($firstIteration) {
                              // Add CSV headers
                              fputcsv($handle, array_keys($data));

                              $firstIteration = false;
                          }
                          // Add a new row with data
                          fputcsv($handle, $data);
                      }
                  });

            // Close the output stream
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
        ]);

        return $response;
    }
}
