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

        $filterLastEncounter = (boolean)$request->input('filterLastEncounter');
        $filterInsurance = (boolean)$request->input('filterInsurance');
        $filterProblems = (boolean)$request->input('filterProblems');

        $generator = new WelcomeCallListGenerator(new Collection($list), $filterLastEncounter, $filterInsurance,
            $filterProblems);

        //If we only want to export ineligible patients
//        return $generator->exportIneligibleToCsv();

        return $generator->exportToCsv();
    }
}
