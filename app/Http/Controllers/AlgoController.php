<?php

namespace App\Http\Controllers;


use App\Algorithms\Calls\SuccessfulHandler;
use App\Algorithms\Calls\UnsuccessfulHandler;
use App\PatientInfo;
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

                $prediction = (new SuccessfulHandler($guineaPig))->getPatientOffset($ccm, $week);

            } else {

                $prediction = (new UnsuccessfulHandler($guineaPig))->getPatientOffset($ccm, $week);

            }


            //get a test patient

            return $prediction;
        }

    }
    

}