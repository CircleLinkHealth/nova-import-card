<?php namespace App\Http\Controllers\EditImportedCcda;

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
        $demographics = $request->input('demographics');

        //create a new row
        $newDemographics = DemographicsImport::whereId($demographics['id'])->update($demographics);

		return response()->json('OK', 201);
	}
}
