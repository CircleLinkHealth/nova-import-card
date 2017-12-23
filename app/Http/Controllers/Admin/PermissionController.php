<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Permission;
use App\Role;
use Auth;
use Illuminate\Http\Request;

class PermissionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403);
        }
        // display view
        $permissions = Permission::paginate(10);
        return view('admin.permissions.index', [ 'permissions' => $permissions ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403);
        }
        // display view
        $roles = Role::all();
        return view('admin.permissions.create', [ 'roles' => $roles, 'errors' => [], 'messages' => [] ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403);
        }
        $params = $request->input();
        $permission = new Permission;
        $permission->name = $params['name'];
        $permission->display_name = $params['display_name'];
        $permission->description = $params['description'];
        $permission->save();
        if (isset($params['roles'])) {
            $permission->roles()->sync($params['roles']);
        }
        $permission->save();
        redirect()->route('admin.permissions.edit', [$permission->id])->with('messages', ['successfully added new permission - '.$params['name']])->send();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403);
        }
        // display view
        $permission = Permission::find($id);
        return view('admin.permissions.show', [ 'permission' => $permission, 'errors' => [], 'messages' => [] ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403);
        }
        $permission = Permission::find($id);
        $roles = Role::all();
        $permissionRoles = $permission->roles()->pluck('id')->all();
        return view('admin.permissions.edit', [ 'permission' => $permission, 'roles' => $roles, 'permissionRoles' => $permissionRoles, 'messages' => \Session::get('messages') ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403);
        }
        $params = $request->input();
        $permission = Permission::find($id);
        $permission->name = $params['name'];
        $permission->display_name = $params['display_name'];
        $permission->description = $params['description'];
        if (isset($params['roles'])) {
            $permission->roles()->sync($params['roles']);
        }
        $permission->save();
        return redirect()->back()->with('messages', ['successfully updated permission'])->send();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasRole('administrator')) {
            abort(403);
        }
        //
    }
}
