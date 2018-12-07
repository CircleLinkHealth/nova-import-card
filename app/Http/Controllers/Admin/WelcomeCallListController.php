<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatientData\Rappa\RappaData;
use App\Models\PatientData\Rappa\RappaInsAllergy;
use App\Models\PatientData\Rappa\RappaName;
use App\Models\PatientData\RockyMountain\RockyData;
use App\Models\PatientData\RockyMountain\RockyName;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\Eligibility\Csv\CsvPatientList;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Http\Request;

class WelcomeCallListController extends Controller
{
    protected $processEligibilityService;

    public function __construct(
        ProcessEligibilityService $processEligibilityService
    ) {
        $this->processEligibilityService = $processEligibilityService;
    }

    /**
     * Create Phoenix Heart Call List from phoenix_heart_* tables.
     */
    public function makePhoenixHeartCallList()
    {
        $batch = $this->processEligibilityService->createPhoenixHeartBatch();

        return link_to_route(
            'eligibility.batch.show',
            'Job Scheduled. Click here to view progress. Make sure you bookmark the link.',
            [$batch->id]
        );
    }

    /**
     * Create Rappahannock Call List from rappa_* tables.
     */
    public function makeRappahannockCallList()
    {
        $rappaNames        = RappaName::get()->keyBy('patient_id');
        $rappaInsAllergies = RappaInsAllergy::get()->keyBy('patient_id');

        $difference   = $rappaInsAllergies->keys()->diff($rappaNames->keys());
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
                if ($d['medication'] && ! $patient['medications']->contains($d['medication'])) {
                    $patient['medications']->push($d['medication']);
                }

                if ($d['condition'] && ! $patient['problems']->contains($d['condition'])) {
                    $patient['problems']->push($d['condition']);
                }

                if ( ! $patient->contains($d['last_name'])) {
                    $patient->put('last_name', $d['last_name']);
                }

                if ( ! $patient->contains($d['first_name'])) {
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
     * Create Rocky Mountain Call List from rocky_* tables.
     */
    public function makeRockyMtnCallList()
    {
        $names = RockyName::get()->keyBy('patient_id');

        $patientList = $names->map(function ($patient) {
            $data = RockyData::where('patient_id', '=', $patient->patient_id)->get();

            $patient = collect($patient->toArray());

            $patient->put('problems', collect());

            foreach ($data as $d) {
                for ($i = 1; $i < 11; ++$i) {
                    if ($d["DIAG${i}"] && ! $patient['problems']->contains($d["DIAG${i}"])) {
                        $patient['problems']->push($d["DIAG${i}"]);
                    }
                }
            }

            return $patient;
        });

        $list = (new WelcomeCallListGenerator($patientList, false, true, true, false));

        $list->exportToCsv();
//        $list->exportIneligibleToCsv();
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array|string
     */
    public function makeWelcomeCallList(Request $request)
    {
        if ( ! $request->hasFile('patient_list')) {
            dd('Please upload a CSV file.');
        }

        if ( ! $request['practice_id']) {
            dd('`practice_id` is a required field.');
        }

        $practiceId = $request->input('practice_id');

        if ('text/csv' !== $request->file('patient_list')->getClientMimeType()) {
            return [
                'errors' => 'This file is not in a CSV format.',
            ];
        }
        $patients = parseCsvToArray($request->file('patient_list'));

        $filterLastEncounter = (bool) $request->input('filterLastEncounter');
        $filterInsurance     = (bool) $request->input('filterInsurance');
        $filterProblems      = (bool) $request->input('filterProblems');

        $csvPatientList = new CsvPatientList(collect($patients));
        $isValid        = $csvPatientList->guessValidator();

        if ( ! $isValid) {
            return [
                'errors' => 'This csv does not match any of the supported templates. you can see supported templates here https://drive.google.com/drive/folders/1zpiBkegqjTioZGzdoPqZQAqWvXkaKEgB',
            ];
        }

        $batch = $this->processEligibilityService
            ->createSingleCSVBatch($patients, $practiceId, $filterLastEncounter, $filterInsurance, $filterProblems);

        return redirect()->route('eligibility.batch.show', [$batch->id]);
    }
}
