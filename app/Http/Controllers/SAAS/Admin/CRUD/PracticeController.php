<?php

namespace App\Http\Controllers\SAAS\Admin\CRUD;

use App\Http\Controllers\Controller;
use App\Location;
use App\Practice;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $practices = Practice::orderBy('id', 'desc')
                             ->whereActive(1)
                             ->authUserCanAccess()
                             ->get();

        return view('saas.admin.practice.index', ['practices' => $practices]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $messages = \Session::get('messages');

        $locations = Location::whereIn('practice_id', auth()->user()->practices->pluck('id')->all())
                             ->pluck('name', 'id')
                             ->all();

        return view('saas.admin.practice.create', compact(['locations', 'errors', 'messages']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $practice = new Practice;

        $practice->name         = str_slug($request['display_name']);
        $practice->display_name = $request['display_name'];
        $practice->clh_pppm     = $request['clh_pppm'];
        $practice->term_days    = $request['term_days'];
        $practice->active       = isset($request['active'])
            ? 1
            : 0;

        $practice->save();

        return redirect()->route('provider.dashboard.manage.locations', ['practiceSlug' => $practice->name])
                         ->with('messages', ['successfully created new program'])->send();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $messages = \Session::get('messages');

        $program = Practice::find($id);

        $locations = $program->locations->all();

        return view('saas.admin.practice.edit', compact(['program', 'locations', 'errors', 'messages']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
