<?php

namespace App\Http\Controllers;

use App\EligibilityBatch;
use App\Enrollee;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class EligibilityBatchController extends Controller
{
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
                            ->join('cpm_problems as p2', 'p2.id', '=', 'enrollees.cpm_problem_2')
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
}
