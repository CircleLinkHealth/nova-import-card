<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WelcomeCallListController extends Controller
{
    public function makeWelcomeCallList(Request $request)
    {

        if (!$request->hasFile('patient_list')) {
            dd('Please upload a CSV file.');
        }

        $list = parseCsvToArray($request->file('patient_list'));

        $generator = new WelcomeCallListGenerator(new Collection($list));

        //If we only want to export ineligible patients
//        return $generator->exportIneligibleToCsv();

        return $generator->exportToCsv();
    }
}
