<?php

namespace App\Http\Controllers;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
        $unprocessed = 'N/A';
        $ineligible  = 'N/A';
        $duplicates  = 'N/A';

        if ($batch->type == EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS) {
            $statuses = Ccda::select(['status', 'deleted_at'])
                            ->withTrashed()
                            ->whereBatchId($batch->id)
                            ->get();

            $unprocessed = $statuses->where('status', Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY)->where('deleted_at',
                null)->count();
            $ineligible  = $statuses->where('status', Ccda::INELIGIBLE)->where('deleted_at', null)->count();
            $duplicates  = $statuses->where('deleted_at', '!=', null)->count();
        } elseif ($batch->type != EligibilityBatch::TYPE_PHX_DB_TABLES) {
            $jobs = EligibilityJob::whereBatchId($batch->id)->get();

            $unprocessed = $jobs->where('status', '<', 2)->count();
            $ineligible  = $jobs->where('status', 3)->where('outcome', EligibilityJob::INELIGIBLE)->count();
            $duplicates  = $jobs->where('status', 3)->where('outcome', EligibilityJob::DUPLICATE)->count();
        }

        $eligible = Enrollee::whereBatchId($batch->id)->whereNull('user_id')->count();
        $practice = Practice::findOrFail($batch->practice_id);

        return view('eligibilityBatch.show',
            compact(['batch', 'unprocessed', 'eligible', 'ineligible', 'duplicates', 'practice']));
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
        return [
            'not started' => EligibilityJob::where('status', '=', 1)->count(),
            'processing'  => EligibilityJob::where('status', '=', 1)->count(),
            'errors'      => EligibilityJob::where('status', '=', 3)->count(),
            'processed'   => EligibilityJob::where('status', '=', 3)->count(),
        ];
    }

    public function downloadEligibleCsv(EligibilityBatch $batch)
    {
        $practice = Practice::findOrFail($batch->practice_id);

        $eligible = Enrollee::select([
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
            'referring_provider_name',
            'problems',
            'p1.name as ccm_condition_1',
            'p2.name as ccm_condition_2',
        ])
                            ->join('cpm_problems as p1', 'p1.id', '=', 'enrollees.cpm_problem_1')
                            ->leftJoin('cpm_problems as p2', 'p2.id', '=', 'enrollees.cpm_problem_2')
                            ->whereBatchId($batch->id)
                            ->whereNull('user_id')
                            ->get()
                            ->toArray();

        $fileName = $practice->display_name . '_' . Carbon::now()->toAtomString();

        return Excel::create($fileName, function ($excel) use ($eligible) {
            $excel->sheet('Eligible Patients', function ($sheet) use ($eligible) {
                $sheet->fromArray($eligible);
            });
        })->download('csv');
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
        $arr = EligibilityJob::select([
            'batch_id',
            'hash',
            'messages',
            'outcome',
            'status',
        ])
                             ->whereBatchId($batch->id)
                             ->get()
                             ->map(function ($j) {
                                 return [
                                     'batch_id' => $j->batch_id,
                                     'hash'     => $j->hash,
                                     'messages' => json_encode($j->messages),
                                     'outcome'  => $j->outcome,
                                     'status'   => $j->getStatus(),
                                 ];
                             })->all();

        $fileName = 'batch_id_' . $batch->id . '_logs_' . Carbon::now()->toAtomString();

        return Excel::create($fileName, function ($excel) use ($arr) {
            $excel->sheet('Sheet', function ($sheet) use ($arr) {
                $sheet->fromArray($arr);
            });
        })->download('csv');
    }
}
