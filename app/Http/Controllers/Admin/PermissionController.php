<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Role;
use App\Permission;
use App\CPRulesPCP;
use App\CPRulesItemMeta;
use App\CPRulesItem;
use App\Http\Controllers\Controller;

use App\WpBlog;
use Illuminate\Http\Request;

class PermissionController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// display view
		$permissions = Permission::all();
		return view('admin.permissions.index', [ 'permissions' => $permissions ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		// display view
		$roles = Role::all();
		return view('admin.permissions.create', [ 'roles' => $roles, 'errors' => array(), 'messages' => array() ]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$params = $request->input();
		$permission = new Permission;
		$permission->name = $params['name'];
		$permission->display_name = $params['display_name'];
		$permission->description = $params['description'];
		$permission->save();
		$permission->roles()->sync($params['roles']);
		$permission->save();
		redirect()->route('permissionsEdit', [$permission->id])->with('messages', ['successfully added new permission - '.$params['name']]);
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
		$permission = Permission::find($id);
		return view('admin.permissions.show', [ 'permission' => $permission, 'errors' => array(), 'messages' => array() ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$permission = Permission::find($id);
		$roles = Role::all();
		$permissionRoles = $permission->roles()->lists('id');
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
		$params = $request->input();
		$permission = Permission::find($id);
		$permission->name = $params['name'];
		$permission->display_name = $params['display_name'];
		$permission->description = $params['description'];
		$permission->roles()->sync($params['roles']);
		$permission->save();
		return redirect()->back()->with('messages', ['successfully updated permission']);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
