<?php namespace App\Http\Controllers;

use App\Location;
use App\Practice;
use Auth;
use Cerberus;
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

        if (!empty($input['emr_direct'])) {
            $saved->emr_direct_address = $input['emr_direct'];
        }

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

        // return $id;
        return view('locations.show', [
            'messages' => $messages,
            'location' => $location,
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

        $blogs = Practice::all();
        $location = Location::find($id);

        return view('locations.edit', [
            'location' => $location,
            'blogs'    => $blogs,
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

        $loc = Location::find($input['id']);
        $loc->update($input);

        if (!empty($input['emr_direct'])) {
            $loc->emr_direct_address = $input['emr_direct'];
        }

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
