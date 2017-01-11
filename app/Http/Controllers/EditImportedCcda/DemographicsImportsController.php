<?php namespace App\Http\Controllers\EditImportedCcda;

use App\Http\Controllers\Controller;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Location;
use App\Models\CCD\QAImportSummary;
use App\User;
use Illuminate\Http\Request;

class DemographicsImportsController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $demographics = $request->input( 'demographics' );

        $newDemographics = DemographicsImport::whereId( $demographics[ 'id' ] )->update( $demographics );

        $provider = User::find($demographics['provider_id']);
        $location = Location::find($demographics['location_id']);

        $summary = QAImportSummary::whereCcdaId( $demographics['ccda_id'] )->first();
        $summary->flag = 0;
        $summary->name = $demographics['first_name'] . ' ' . $demographics['last_name'];
        $summary->provider = $provider->display_name;
        $summary->location = $location->name;
        $summary->save();


        return response()->json( 'OK', 201 );
    }
}
