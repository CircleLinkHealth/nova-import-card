<?php namespace App\Http\Controllers\Importer;

use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\QAImportSummary;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class DemographicsImportsController extends Controller {

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$substitutedId = $request->input('substitutedId');
        $demographics = $request->input('demographics');

        //create a new row
        $newDemographics = DemographicsImport::create($demographics);

        //delete the old row and mark it as substituted
        $oldRecord = DemographicsImport::find($substitutedId);
        $oldRecord->substitute_id = $newDemographics->id;
        $oldRecord->save();
        $oldRecord->delete();

		return response()->json('OK', 201);
	}
}
