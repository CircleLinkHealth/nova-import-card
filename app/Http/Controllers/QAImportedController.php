<?php namespace App\Http\Controllers;


use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\CCD\ImportedItems\ProblemImport;
use App\CLH\CCD\ItemLogger\CcdProviderLog;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Location;
use App\User;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

use Illuminate\Http\Request;

class QAImportedController extends Controller
{

    public function edit(Request $request, $ccdaId)
    {
        $demographics = DemographicsImport::whereCcdaId( $ccdaId )->first();
        $allergies = AllergyImport::whereCcdaId( $ccdaId )->get();
        $locations = Location::whereNotNull( 'parent_id' )->get();
        $medications = MedicationImport::whereCcdaId( $ccdaId )->get();
        $problems = ProblemImport::whereCcdaId( $ccdaId )->get();
        $providers = User::whereHas( 'roles', function ($q) {
            $q->where( 'name', '=', 'provider' );
        } )->get();

        $providers = $providers->map( function ($provider) {
            return [
                'id' => $provider->ID,
                'name' => $provider->display_name
            ];
        } );

        $locations = $locations->map(function ($loc){
            return [
                'id' => $loc->id,
                'name' => $loc->name
            ];
        });

        JavaScript::put( [
            'demographics' => $demographics,
            'allergies' => $allergies,
            'locations' => $locations,
            'medications' => $medications,
            'problems' => $problems,
            'providers' => $providers,
        ] );

        return view( 'CCDUploader.editUploadedItems' );
    }

}
