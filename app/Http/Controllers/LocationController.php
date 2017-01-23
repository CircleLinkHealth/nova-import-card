<?php namespace App\Http\Controllers;

use App\Location;
use App\Practice;
use Auth;
use Entrust;
use Illuminate\Http\Request;

class LocationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('locations-view')) {
            abort(403);
        }
        $messages = \Session::get('messages');

        return view('locations.index', [
            'locations' => Location::all(),
            'messages'  => $messages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Auth::user()->can('locations-manage')) {
            abort(403);
        }

        $practices = Practice::all();

        return view('locations.create', compact('practices'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('locations-manage')) {
            abort(403);
        }

        $input = $request->input();

        $newLocation = new Location($input);
        $saved = $newLocation->save();

        return $saved
            ?
            redirect()->route('locations.index')->with('messages', ['Location Created!!'])
            :
            response('Error', 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('locations-view')) {
            abort(403);
        }
        $messages = \Session::get('messages');
        $location = Location::find($id);
        if (is_null($location->parent_id)) {
            $locationParents = null;
        } else {
            $locationParents = Location::getParents($id);
        }

        // return $id;
        return view('locations.show', [
            'messages'            => $messages,
            'location'            => $location,
            'locationParents'     => $locationParents,
            'locationSubs'        => Location::getNonRootLocations(),
            'locationParentsSubs' => Location::getParentsSubs($id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        if (!Auth::user()->can('locations-manage')) {
            abort(403);
        }
        debug(Location::getParents($id));
        $blogs = Practice::all();

        return view('locations.edit', [
            'location'  => Location::find($id),
            'locations' => Location::getParents($id),
            'blogs'     => $blogs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update(Request $request)
    {
        if (!Auth::user()->can('locations-manage')) {
            abort(403);
        }

        $input = $request->all();

        if (empty($input['parent_id'])) {
            $input['position'] = 0;
        } else {
            $input['position'] = 1;
        }

        if (empty($input['parent_id'])) {
            $input['parent_id'] = null;
        }

        Location::find($input['id'])->update($input);

        return redirect()->route('locations.index')->with('messages', ['Location Updated!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('locations-manage')) {
            abort(403);
        }
        Location::destroy($id);

        return 'deleted ' . $id;
    }

}
