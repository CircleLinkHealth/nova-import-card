<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EhrReportWriterController extends Controller
{
    public function index(){

        return view('ehrReportWriter.index');
    }

    public function downloadEligibleFormat(Request $request){

    }

    public function validateJson(Request $request){

    }

    public function submitFile(Request $request){

    }
}
