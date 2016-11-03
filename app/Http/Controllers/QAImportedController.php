<?php namespace App\Http\Controllers;


use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\CCD\ImportedItems\ProblemImport;
use App\Models\CCD\CcdVendor;
use App\Practice;
use App\User;
use Illuminate\Http\Request;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class QAImportedController extends Controller
{

    public function edit(Request $request, $ccdaId)
    {
        $demographics = DemographicsImport::whereCcdaId( $ccdaId )->first();
        $allergies = AllergyImport::whereCcdaId( $ccdaId )->get();
        $medications = MedicationImport::whereCcdaId( $ccdaId )->get();
        $problems = ProblemImport::whereCcdaId( $ccdaId )->get();

        $programId = $demographics->program_id;
        $programObj = Practice::find($programId);

        //get program's location
        $locations = $programObj->locations;

        $vendor = CcdVendor::find($demographics->vendor_id);

        $providers = User::ofType('provider')
            ->whereProgramId($programId)
            ->get();

        $providers = $providers->map( function ($provider) {
            return [
                'id'   => $provider->id,
                'name' => $provider->display_name
            ];
        } );

        $locations = $locations->map( function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->name
            ];
        } );

        $program = [
            'id' => $programId,
            'name' => $programObj->display_name,
            'domain' => $programObj->domain,
        ];

        JavaScript::put( [
            'demographics' => $demographics,
            'allergies' => $allergies,
            'locations' => $locations,
            'medications' => $medications,
            'problems' => $problems,
            'program' => $program,
            'providers' => $providers,
            'vendor' => $vendor->vendor_name,
        ] );

        return view( 'CCDUploader.editUploadedItems' );
    }

}
