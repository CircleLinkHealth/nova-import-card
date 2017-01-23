<?php

namespace App\Http\Controllers\Patient;

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

    public function index(){

        $enrolled = Patient::where('ccm_status', 'consented')->with('user')->get();

        $formatted = [];
        $count = 0;

        foreach ($enrolled as $patient){

            $formatted[$count] = [

                'name' => $patient->user->fullName,
                'program' => ucwords(Practice::find($patient->user->program_id)->name),
                'dob' => $patient->birth_date,
                'date' => $patient->consent_date,
                'phone' => $patient->user->primaryPhone

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

            'user_id' => $enrollee->id,
            'type' => 'work',
            'number' => $input['phone'],
            'is_primary' => 1

        ]);

        $phone->save();
        $enrollee->phoneNumbers()->save($phone);

        dd([$enrollee, $patient]);


    }

}
