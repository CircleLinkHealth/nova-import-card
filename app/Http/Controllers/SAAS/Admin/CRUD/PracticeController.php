<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\SAAS\Admin\CRUD;

use App\Http\Controllers\Controller;
use App\Http\Requests\SAAS\StorePractice;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PracticeController extends Controller
{
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

        return view('saas.admin.practice.create', compact(['locations', 'messages']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = 'active';

        if ($request->has('filter')) {
            $filter = $request->input('filter', 'all');
        }

        switch ($filter) {
            case 'all':
                $filterOptions = [0, 1];
                break;
            case 'active':
                $filterOptions = [1];
                break;
            case 'inactive':
                $filterOptions = [0];
                break;
            default:
                $filterOptions = [0, 1];
                break;
        }

        $practices = Practice::orderBy('id', 'desc')
            ->whereIn('active', $filterOptions)
            ->authUserCanAccess()
            ->get();

        return view('saas.admin.practice.index')
            ->with('practices', $practices)
            ->with('filter', $filter);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StorePractice $request)
    {
        $saasAccount = auth()->user()->saasAccount;

        $practice = Practice::where('name', $saasAccount->name)
            ->where('saas_account_id', $saasAccount->id)
            ->first();

        if ( ! $practice) {
            $practice = new Practice();
        }

        $practice->saas_account_id = $saasAccount->id;
        $practice->name            = Str::slug($request['display_name']);
        $practice->display_name    = $request['display_name'];
        $practice->term_days       = $request['term_days'];
        $practice->clh_pppm        = $request['amount'];
        $practice->active          = isset($request['active'])
            ? 1
            : 0;

        $practice->save();

        $practice->chargeableServices()
            ->attach($request['service_id'], [
                'amount' => $request['amount'],
            ]);

        return redirect()->route('provider.dashboard.manage.locations', ['practiceSlug' => $practice->name])
            ->with('messages', ['successfully created new program']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }
}
