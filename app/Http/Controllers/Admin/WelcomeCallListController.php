<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Practice;
use App\Rappa\RappaData;
use App\Rappa\RappaInsAllergy;
use App\Rappa\RappaName;
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
            $filterProblems, $createEnrollees, Practice::find($request->input('practice_id')));

        //If we only want to export ineligible patients
//        return $list->exportIneligibleToCsv();

        return $list->exportToCsv();
    }

    /**
     * Create Rappahannock Call List from rappa_* tables
     */
    public function makeRappahannockCallList()
    {
        $rappaNames = RappaName::get()->keyBy('patient_id');
        $rappaInsAllergies = RappaInsAllergy::get()->keyBy('patient_id');

        $difference = $rappaInsAllergies->keys()->diff($rappaNames->keys());
        $intersection = $rappaInsAllergies->keys()->intersect($rappaNames->keys());

        $merged = $rappaInsAllergies->map(function ($rappaInsAllergy) use ($rappaNames) {
            if ($name = $rappaNames->get($rappaInsAllergy->patient_id)) {
                return collect($name)->merge($rappaInsAllergy);
            }

            return collect($rappaInsAllergy);
        });


        $patientList = $merged->map(function ($patient) {
            $data = RappaData::where('patient_id', '=', $patient->get('patient_id'))->get();

            $patient->put('medications', collect());
            $patient->put('problems', collect());

            foreach ($data as $d) {
                if ($d['medication'] && !$patient['medications']->contains($d['medication'])) {
                    $patient['medications']->push($d['medication']);
                }

                if ($d['condition'] && !$patient['problems']->contains($d['condition'])) {
                    $patient['problems']->push($d['condition']);
                }

                if (!$patient->contains($d['last_name'])) {
                    $patient->put('last_name', $d['last_name']);
                }

                if (!$patient->contains($d['first_name'])) {
                    $patient->put('first_name', $d['first_name']);
                }
            }

            return $patient;
        });

        $list = (new WelcomeCallListGenerator($patientList, true, true, true, false));

        $list->exportToCsv();
//        $list->exportIneligibleToCsv();
    }
}
