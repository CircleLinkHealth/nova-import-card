<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatientData\Rappa\RappaData;
use App\Models\PatientData\Rappa\RappaInsAllergy;
use App\Models\PatientData\Rappa\RappaName;
use App\Models\PatientData\RockyMountain\RockyData;
use App\Models\PatientData\RockyMountain\RockyName;
use App\Practice;
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

    /**
     * Create Rocky Mountain Call List from rappa_* tables
     */
    public function makeRockyMtnCallList()
    {
        $names = RockyName::get()->keyBy('patient_id');

        $patientList = $names->map(function ($patient) {
            $data = RockyData::where('patient_id', '=', $patient->patient_id)->get();

            $patient = collect($patient->toArray());

            $patient->put('problems', collect());

            foreach ($data as $d) {
                for ($i = 1; $i < 11; $i++) {
                    if ($d["DIAG$i"] && !$patient['problems']->contains($d["DIAG$i"])) {
                        $patient['problems']->push($d["DIAG$i"]);
                    }
                }
            }

            return $patient;
        });

        $list = (new WelcomeCallListGenerator($patientList, false, true, true, false));

        $list->exportToCsv();
//        $list->exportIneligibleToCsv();
    }
}
