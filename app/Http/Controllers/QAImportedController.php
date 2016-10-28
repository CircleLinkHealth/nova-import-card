<?php namespace App\Http\Controllers;


use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\CCD\ImportedItems\ProblemImport;
use App\Location;
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
        $programObj = Practice::whereBlogId($programId)->first();

        //get program's location
        $locations = Location::whereNotNull( 'parent_id' )->whereId( $programObj->location_id )->first();

        if (empty($locations)){
            //means it's a parent loc
            $locations = Location::whereParentId( $programObj->location_id )->get();
        } else {
            //get all locations from the same parent
            $locations = Location::whereParentId( $locations->parent_id )->get();
        }

        $vendor = CcdVendor::find($demographics->vendor_id);

        $providers = User::whereHas( 'roles', function ($q) {
            $q->where( 'name', '=', 'provider' );
        } )->whereProgramId( $programId )
            ->get();

        $providers = $providers->map( function ($provider) {
            return [
                'id' => $provider->ID,
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
