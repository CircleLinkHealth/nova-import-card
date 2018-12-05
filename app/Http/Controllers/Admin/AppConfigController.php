<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\AppConfig;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppConfigController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // display view
        return view('admin.appConfig.create', ['errors' => [], 'messages' => []]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $appConfig = AppConfig::find($id);
        if ( ! $appConfig) {
            return response('User not found', 401);
        }

        //$user->practices()->detach();
        $appConfig->delete();

        return redirect()->back()->with('messages', ['successfully deleted app config']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $appConfig = AppConfig::find($id);

        return view('admin.appConfig.edit', ['appConfig' => $appConfig, 'messages' => \Session::get('messages')]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // display view
        $appConfigs = AppConfig::OrderBy('config_key', 'asc')->paginate(10);

        return view('admin.appConfig.index', ['appConfigs' => $appConfigs]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        // display view
        $appConfig = AppConfig::find($id);

        return view('admin.appConfig.show', ['appConfig' => $appConfig, 'errors' => [], 'messages' => []]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params                  = $request->input();
        $appConfig               = new AppConfig();
        $appConfig->config_key   = $params['config_key'];
        $appConfig->config_value = $params['config_value'];
        $appConfig->save();

        return redirect()->route('admin.appConfig.edit', [$appConfig->id])->with(
            'messages',
            ['successfully added new app config - '.$params['config_key']]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $params                  = $request->input();
        $appConfig               = AppConfig::find($id);
        $appConfig->config_key   = $params['config_key'];
        $appConfig->config_value = $params['config_value'];
        $appConfig->save();

        return redirect()->back()->with('messages', ['successfully updated app config']);
    }
}
