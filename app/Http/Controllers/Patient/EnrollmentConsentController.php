<?php

namespace App\Http\Controllers\Patient;

use App\Call;
use App\CLH\Helpers\StringManipulation;
use App\Enrollee;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Patient;
use App\PhoneNumber;
use App\Practice;
use App\Role;
use Carbon\Carbon;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class EnrollmentConsentController extends Controller
{

    /**
     * @return mixed
     */
    public function index(){

        //todo change to Enrollee
        $enrolled = Patient::where('ccm_status', 'consented')->with('user')->get();

        $formatted = [];
        $count = 0;

        foreach ($enrolled as $patient){
            
            $medicalRecord = ImportedMedicalRecord::where('patient_id', $patient->user->id)->first();
            $scheduledCall = Call
                ::where('inbound_cpm_id', $patient->user->id)
                ->where('status', '=', 'scheduled')
                ->first();

            if($medicalRecord == null){
                $medicalRecord = 'N/A';
            } else {
                $medicalRecord = 'Exists';
            }

            if($scheduledCall == null){
                $scheduledCall = 'N/A';
            } else {
                $scheduledCall = $scheduledCall->scheduled_date;
            }

            $formatted[$count] = [

                'name' => $patient->user->fullName,
                'program' => ucwords(Practice::find($patient->user->program_id)->name),
                'dob' => $patient->birth_date,
                'date' => $patient->consent_date,
                'phone' => $patient->user->primaryPhone,
                'hasCallScheduled' => $scheduledCall,
                'hasMedicalRecord' => $medicalRecord

            ];
            $count++;

        }

        $formatted = collect($formatted);
        $formatted->sortByDesc('date');


        return Datatables::collection($formatted)->make(true);

    }

    public function makeEnrollmentReport()
    {

        return view('admin.reports.enrollment-list');

    }

    public function create($invite_code){

        $enrollee = Enrollee::whereInviteCode($invite_code)->first();
        $enrollee->invite_opened_at = Carbon::now()->toDateTimeString();
        $enrollee->save();

        if(is_null($enrollee)){

            return view('errors.enrollmentConsentUrlError');

        }

        return view('enrollment-consent.create', ['enrollee' => $enrollee, 'has_copay' => true]);

    }

    public function store(Request $request){

        $input = $request->input();

        $enrollee = Enrollee::find($input['enrollee_id']);

        $enrollee->consented_at = Carbon::parse($input['consented_at'])->toDateTimeString();
        $enrollee->status = 'consented';
        $enrollee->save();

        return json_encode($enrollee);

    }

    public function update(Request $request){

        $input = $request->input();

        $enrollee = Enrollee::find($input['enrollee_id']);

        if(isset($input['days'])) {
            $enrollee->preferred_days = implode(', ', $input['days']);

        }

        if(isset($input['time'])) {

            $enrollee->preferred_window = $input['time'];
        }

        $enrollee->save();

        return view('enrollment-consent.thanks', ['enrollee' => $enrollee]);


    }

}
