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
            $week = $request->input('week');
            $status = (bool) $request->input('status');
            $successThisMonth = (bool) $request->input('call_success');

            $guineaPig = PatientInfo::find(1272);

            if($status){

                //Pass in a patient, and a time to start calculations.

                //in this case, the calculation is the first day of the given months's week.
                //so, if we simulate week 3, it will go to the first day of the month, and
                //add a 3 weeks to it.

                $day = (new SuccessfulHandler($guineaPig,
                                              Carbon::now()
                                                    ->startOfMonth()
                                                    ->addWeeks($week - 1 )))
                                    ->getPatientOffset($ccm, $week);

                return $day->format('jS M');

            } else {


                $day = (new UnsuccessfulHandler($guineaPig,
                                                Carbon::now()
                                                    ->startOfMonth()
                                                    ->addWeeks($week - 1 )))
                                    ->getPatientOffset($ccm, $week);

                return $day->format('jS M');

            }

        }

    }
    

}