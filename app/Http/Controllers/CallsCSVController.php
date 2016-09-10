<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class CallsCSVController extends Controller
{
    public function uploadCSV(Request $request)
    {
        if ($request->hasFile('uploadedCsv')) {
            $csv = parseCsvToArray($request->file('uploadedCsv'));

            $patients = array();
            
            foreach ($csv as $patient){

                $temp = User::where('first_name', $patient['Patient First Name'])
                                  ->where('last_name', $patient['Patient Last Name'])
                                  ->pluck('id');

                if(is_object($temp)) $patients[] = $temp;

            }

            return $patients;
        }
    }
}
