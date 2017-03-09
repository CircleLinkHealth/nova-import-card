<?php

namespace App\Http\Controllers;

use App\Enrollee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnrollmentCenterController extends Controller
{

    public function dashboard()
    {

        //get an eligible patient.
        $enrollee = Enrollee::toCall()->first();

        if($enrollee == null){

            //no calls available
            return view('enrollment-ui.no-available-calls');

        }

        return view('enrollment-ui.dashboard',
            [
                'enrollee' => $enrollee,
            ]
        );

    }

    public function consented(Request $request)
    {

        //update details
//        dd($request->input());

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        $enrollee->primary_phone = $request->input('primary_phone');
        $enrollee->home_phone = $request->input('home_phone');
        $enrollee->cell_phone = $request->input('cell_phone');
        $enrollee->other_phone = $request->input('other_phone');
        $enrollee->address = $request->input('address');
        $enrollee->address_2 = $request->input('address_2');
        $enrollee->state = $request->input('state');
        $enrollee->city = $request->input('city');
        $enrollee->zip = $request->input('zip');
        $enrollee->email = $request->input('email');
        $enrollee->dob = $request->input('dob');
        $enrollee->last_call_outcome = $request->input('consented');
        $enrollee->care_ambassador_id = auth()->user()->id;

        if ($request->input('extra')) {
            $enrollee->last_call_outcome_reason = $request->input('extra');
        }

        if (is_array($request->input('days'))) {
            $enrollee->preferred_days = implode(', ', $request->input('days'));

        }

        if (is_array($request->input('times'))) {

            $enrollee->preferred_window = implode(', ', $request->input('times'));
        }

        $enrollee->status = 'consented';
        $enrollee->consented_at = Carbon::now()->toDateTimeString();
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();

        return redirect()->action('EnrollmentCenterController@dashboard');

    }

    public function unableToContact(Request $request)
    {

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        return redirect()->action('EnrollmentCenterController@dashboard');


    }

    public function rejected(Request $request)
    {

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        return redirect()->action('EnrollmentCenterController@dashboard');

    }

    public function training()
    {

        return view('enrollment-ui.training');

    }

}
