<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class LocationController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// if ($request->header('X-Authorization'))
		// {
		// 	return response()->json(Crypt::encrypt( json_encode(Location::getNonRootLocations()) ));
		// }
		// else
		// {
		return view('locations.show', [ 
			'locationParents' => Location::getAllParents(),
			'locationSubs' => Location::getNonRootLocations(),
			'locationParentsSubs' => Location::getParentsSubs($request)	 
			]);
		// }
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return view('locations.create', [ 'locations' => Location::getAllNodes() ]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$input = $request->input();

		$newLocation = new Location( $input );
		$saved = $newLocation->save();

		if ( ! empty( $input['parent_id'] ) ) {
			$parent = Location::find( $input['parent_id'] );
			$parent->addChild($newLocation);
		}

		return $saved ?
			response('Location created', 201) :
			response('Error', 500);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		// return $id;
		return view('locations.show', [ 
			'locationParents' => Location::getAllParents(),
			'locationSubs' => Location::getNonRootLocations(),
			'locationParentsSubs' => Location::getParentsSubs($id)	 
			]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Location::destroy($id);

		return 'deleted ' . $id;
	}

}
