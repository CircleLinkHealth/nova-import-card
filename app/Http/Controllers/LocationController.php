<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Location;
use App\WpBlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Entrust;

class LocationController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		if(!Auth::user()->can('locations-view')) {
			abort(403);
		}
		if ( $request->header('Client') == 'ui' ) // WP Site
		{
			$parent_location_id = Crypt::decrypt($request->header('parent-location-id'));
			$locations = Location::getNonRootLocations($parent_location_id);
			if($locations) {
				return response()->json( Crypt::encrypt(json_encode($locations)) );
			} else {
				return response("Locations not found", 401);
			}
		} else {
			$messages = \Session::get('messages');
			return view('locations.index', [
				'locationParents' => Location::getAllParents(),
				'locationSubs' => Location::getNonRootLocations(),
				'messages' => $messages,
				'locationParentsSubs' => Location::getParentsSubs($request)
			]);
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if(!Auth::user()->can('locations-manage')) {
			abort(403);
		}
		$blogs = WpBlog::all();
		return view('locations.create', [ 'locations' => Location::getAllNodes(), 'blogs' => $blogs ]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		if(!Auth::user()->can('locations-manage')) {
			abort(403);
		}
		$input = $request->input();

		$newLocation = new Location( $input );
		$saved = $newLocation->save();

		if ( ! empty( $input['parent_id'] ) ) {
			$parent = Location::find( $input['parent_id'] );
			$parent->addChild($newLocation);
		}

		return $saved ?
			redirect()->route('locations.index')->with('messages', ['Location Created!!']) :
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
		if(!Auth::user()->can('locations-view')) {
			abort(403);
		}
		$messages = \Session::get('messages');
		// return $id;
		return view('locations.show', [
			'messages' => $messages,
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
		if(!Auth::user()->can('locations-manage')) {
			abort(403);
		}
		$blogs = WpBlog::all();
		return view('locations.edit', ['location' => Location::find($id), 'locations' => Location::getAllNodes(), 'blogs' => $blogs]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request)
	{
		if(!Auth::user()->can('locations-manage')) {
			abort(403);
		}

		$input = $request->all();

		Location::find($input['id'])->update($input);

		return redirect()->route('locations.index')->with('messages', ['Location Updated!!']);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if(!Auth::user()->can('locations-manage')) {
			abort(403);
		}
		Location::destroy($id);

		return 'deleted ' . $id;
	}

}
