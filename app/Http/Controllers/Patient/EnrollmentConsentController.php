<?php

namespace App\Http\Controllers\Patient;

use App\Call;
use App\CLH\Helpers\StringManipulation;
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

    public function create($practice_name){

        $practice = Practice::whereName($practice_name)->first();

        if(is_null($practice)){

            return view('errors.enrollmentConsentUrlError');

        }

        return view('enrollment-consent.create', ['practice' => $practice]);

    }

    public function store(Request $request){

        $input = $request->input();

        $enrolled_date = Carbon::parse($input['enrolled_time'])->toDateTimeString();
        $confirmed_date = Carbon::parse($input['confirmed_time'])->toDateTimeString();


        //Check if patient exists.
        $phone = $input['phone'];

        $exists = User
            ::where('first_name', trim($input['first_name']))
            ->where('last_name', trim($input['last_name']))
            ->whereHas('phoneNumbers', function ($q) use ($phone){

                $q->where('number', $phone);

            })->first();

        if($exists){

            $exists->patientInfo->consent_date = $confirmed_date;
            $exists->patientInfo->registration_date = $enrolled_date;

            $exists->patientInfo->save();

            debug('Exists');

        } else {

            //Create User
            $enrollee = new User;
            $enrollee->program_id = $input['practice_id'];
            $enrollee->first_name = $input['first_name'];
            $enrollee->last_name = $input['last_name'];
            $enrollee->save();

            //Create Patient
            $patient = new Patient([
                'user_id' => $enrollee->id
            ]);

            $patient->ccm_status = 'consented';
            $patient->consent_date = $confirmed_date;
            $patient->registration_date = $enrolled_date;
            $patient->birth_date = $input['dob'];
            $patient->save();

            //Attach Role
            $role = Role::whereName('participant')->first();
            $enrollee->attachRole($role);
            $enrollee->save();

            //Create phone
            $phone = new PhoneNumber([

                'user_id'    => $enrollee->id,
                'type'       => 'work',
                'number'     => $input['phone'],
                'is_primary' => 1

            ]);

            $phone->save();
            $enrollee->phoneNumbers()->save($phone);
            
            debug('Does not exist');

        }

        return view('enrollment-consent.thanks', ['name' => $input['first_name']]);


    }

}
