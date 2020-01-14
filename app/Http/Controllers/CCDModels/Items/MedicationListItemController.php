<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CCDModels\Items;

use App\Http\Controllers\Controller;
use CircleLinkHealth\SharedModels\Entities\Medication;
use Illuminate\Http\Request;

/**
 * Class MedicationListItemController.
 */
class MedicationListItemController extends Controller
{
    public function destroy(Request $request)
    {
        $medication = $request->input('medication');
        if ( ! empty($medication)) {
            $ccdMedication = Medication::find($medication['id']);
            if ( ! $ccdMedication) {
                return response('Medication '.$medication['id'].' not found', 401);
            }
            $ccdMedication->delete();
        }

        return response('Successfully removed Medication');
    }

    public function index(Request $request)
    {
        $data           = [];
        $patientId      = $request->input('patient_id');
        $ccdMedications = Medication::where('patient_id', '=', $patientId)->orderBy('name')->get();
        if ($ccdMedications->count() > 0) {
            foreach ($ccdMedications as $ccdMedication) {
                $data[] = [
                    'id'         => $ccdMedication->id,
                    'patient_id' => $ccdMedication->patient_id,
                    'name'       => $ccdMedication->name,
                    'sig'        => $ccdMedication->sig, ];
            }
        }
        // return a JSON response
        return response()->json($data);
    }

    public function store(Request $request)
    {
        // pass back some data, along with the original data, just to prove it was received
        $medication = $request->input('medication');
        if ( ! empty($medication)) {
            $ccdMedication             = new Medication();
            $ccdMedication->patient_id = $medication['patient_id'];
            $ccdMedication->name       = $medication['name'];
            $ccdMedication->sig        = $medication['sig'];
            $ccdMedication->save();
            $id = $ccdMedication;
        }
        $result = ['id' => $id];
        // return a JSON response
        return response()->json($result);
    }

    public function update(Request $request)
    {
        // pass back some data, along with the original data, just to prove it was received
        $medication = $request->input('medication');
        if ( ! empty($medication)) {
            $ccdMedication = Medication::find($medication['id']);
            if ( ! $ccdMedication) {
                return response('Medication not found', 401);
            }
            $ccdMedication->name = $medication['name'];
            $ccdMedication->sig  = $medication['sig'];
            $ccdMedication->save();
        }
        $string = '';
        // return a JSON response
        return response()->json($string);
    }
}
