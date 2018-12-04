<?php namespace App\Http\Controllers;

use App\Location;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Practice;
use App\User;
use Illuminate\Http\Request;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class ImportedMedicalRecordController extends Controller
{
    public function edit(
        Request $request,
        $importedMedicalRecordId
    ) {
        $imr = ImportedMedicalRecord::find($importedMedicalRecordId);

        $demographics = $imr->demographics;
        $allergies = $imr->allergies->all();
        $medications = $imr->medications->all();
        $problems = $imr->problems->all();

        $practiceId = $imr->practice_id;

        if ($practiceId) {
            $practiceObj = Practice::find($practiceId);

            //get program's location
            $locations = $practiceObj->locations;

            $providers = User::ofType('provider')
                ->whereProgramId($practiceId)
                ->get();

            $providers = $providers->map(function ($provider) {
                return [
                    'id'   => $provider->id,
                    'name' => $provider->display_name,
                ];
            });

            $locations = $locations->map(function ($loc) {
                return [
                    'id'   => $loc->id,
                    'name' => $loc->name,
                ];
            });

            $practice = [
                'id'     => $practiceId,
                'name'   => $practiceObj->display_name,
                'domain' => $practiceObj->domain,
            ];
        } else {
            $locations = Location::all();
            $practice = Practice::all();
            $providers = User::ofType('provider')->get();
        }

        JavaScript::put([
            'demographics' => $demographics,
            'allergies'    => $allergies,
            'locations'    => $locations ?? null,
            'medications'  => $medications,
            'problems'     => $problems,
            'program'      => $practice ?? null,
            'providers'    => $providers ?? null,
            'vendor'       => 'Deprecated',
        ]);

        return view('CCDUploader.editUploadedItems');
    }
}
