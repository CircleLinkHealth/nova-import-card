<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Location;
use App\Practice;
use Illuminate\Http\Request;

class PracticeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        // display view
        $wpBlogs = Practice::orderBy('id', 'desc')->whereActive(1)->get();

        return view('admin.wpBlogs.index', [ 'wpBlogs' => $wpBlogs ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

        $messages = \Session::get('messages');

        $locations = Location::all()
            ->pluck('name', 'id')
            ->all();

        return view('admin.wpBlogs.create', compact([ 'locations', 'errors', 'messages' ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {

        // get params
        $params = $request->input();

        $program = new Practice;

        $program->name = str_slug($params['display_name']);
        $program->display_name = $params['display_name'];
        $program->weekly_report_recipients = $params['weekly_report_recipients'];
        $program->invoice_recipients = $params['invoice_recipients'];
        $program->clh_pppm = $params['clh_pppm'];
        $program->term_days = $params['term_days'];
        $program->bill_to_name = $params['bill_to_name'];

        $program->active = isset($params['active']) ? 1 : 0;

        $program->save();

        return redirect()->route('admin.programs.edit', ['program' => $program])->with('messages',
            ['successfully created new program']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        // display view
        $program = Practice::find($id);

        //to update...
        $locations = $program->locations->pluck('name', 'id')->all();

        return view('admin.wpBlogs.show', compact([ 'program', 'locations', 'errors', 'messages' ]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

        $messages = \Session::get('messages');

        $program = Practice::find($id);

        $locations = $program->locations->all();

        return view('admin.wpBlogs.edit', compact([ 'program', 'locations', 'errors', 'messages' ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        // find program
        $program = Practice::find($id);
        if (!$program) {
            abort(400);
        }
        // get params
        $params = $request->input();

        isset($params['location_id']) ? $program->locations->attach($params['location_id']) : '';

        Location::setPrimary(Location::find($params['primary_location']));

        $program->display_name = $params['display_name'];
        $program->clh_pppm = $params['clh_pppm'];
        $program->term_days = $params['term_days'];
        $program->bill_to_name = $params['bill_to_name'];

        $program->active = isset($params['active']) ? 1 : 0;

        $program->save();
        return redirect()->back()->with('messages', ['successfully updated program']);
    }
}
