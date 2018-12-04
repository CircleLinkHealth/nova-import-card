<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Permission;
use App\Role;
use Auth;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        // display view
        $permissions = Permission::all();

        return view('admin.roles.create', [
            'permissions' => $permissions,
            'errors'      => [],
            'messages'    => [],
        ]);
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
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
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
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $role        = Role::find($id);
        $permissions = Permission::OrderBy('name', 'asc')->get();

        return view('admin.roles.edit', [
            'role'        => $role,
            'permissions' => $permissions,
            'messages'    => \Session::get('messages'),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        // display view
        $roles = Role::OrderBy('name', 'asc')->paginate(10);

        return view('admin.roles.index', ['roles' => $roles]);
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
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        // display view
        $role = Role::find($id);

        return view('admin.roles.show', [
            'role'     => $role,
            'errors'   => [],
            'messages' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $params             = $request->input();
        $role               = new Role();
        $role->name         = $params['name'];
        $role->display_name = $params['display_name'];
        $role->description  = $params['description'];
        $role->save();
        if (isset($params['permissions'])) {
            $role->perms()->sync($params['permissions']);
        }
        $role->save();

        return redirect()->route('roles.edit', [$role->id])->with(
            'messages',
            ['successfully added new role - '.$params['name']]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update(
        Request $request,
        $id
    ) {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $params             = $request->input();
        $role               = Role::find($id);
        $role->name         = $params['name'];
        $role->display_name = $params['display_name'];
        $role->description  = $params['description'];
        if (isset($params['permissions'])) {
            $role->perms()->sync($params['permissions']);
        }
        $role->save();

        return redirect()->back()->with('messages', ['successfully updated role']);
    }
}
