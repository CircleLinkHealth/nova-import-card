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

        $csv = parseCsvToArray($request->file('patient_list'));

        $filterLastEncounter = (boolean)$request->input('filterLastEncounter');
        $filterInsurance = (boolean)$request->input('filterInsurance');
        $filterProblems = (boolean)$request->input('filterProblems');
        $createEnrollees = (boolean)$request->input('createEnrollees');

        $list = new WelcomeCallListGenerator(new Collection($csv), $filterLastEncounter, $filterInsurance,
            $filterProblems, $createEnrollees);

        //If we only want to export ineligible patients
//        return $list->exportIneligibleToCsv();

        return $list->exportToCsv();
    }
}
