<?php

namespace App\Http\Controllers;


use App\Algorithms\Calls\SuccessfulHandler;
use App\Algorithms\Calls\UnsuccessfulHandler;
use App\PatientInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlgoController extends Controller
{

    public function createMock(Request $request){


        return view('admin.algo.mocker');
        
    }

    public function computeMock(Request $request){

        if($request->ajax()){

            $ccm = $request->input('seconds');
            $date = Carbon::parse($request->input('date'));
            $status = (bool) $request->input('status');
            $contact_day = $request->input('days');

            $guineaPig = PatientInfo::find(1272);

            if($status){

                //Pass in a patient, and a time to start calculations.

                //in this case, the calculation is the first day of the given months's week.
                //so, if we simulate week 3, it will go to the first day of the month, and
                //add a 3 weeks to it.

                $day = (new SuccessfulHandler($guineaPig, $date))
                                    ->getPatientOffset($ccm, $date->weekOfMonth);



            } else {


                $day = (new UnsuccessfulHandler($guineaPig,$date))
                                    ->getPatientOffset($ccm, $date->weekOfMonth);


            }

            $days = [];

            foreach ($contact_day as $week_day){
                $days[] = Carbon::parse($day)->next($week_day);
            }

            $upcoming = min($days);

            return $upcoming->format('l, jS M');

        }

    }
    

}