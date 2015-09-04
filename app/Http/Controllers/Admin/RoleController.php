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

class RoleController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// display view
		$roles = Role::all();
		return view('admin.roles.index', [ 'roles' => $roles ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
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
		$role = Role::find($id);
		dd($role->permissions());
		/*
		$cPRulesPCP = CPRulesPCP::where('prov_id', '=', $id)->where('status', '=', 'Active')->with('items.meta')->get();
		*/
		return view('admin.roles.edit', [ 'role' => $role, 'errors' => array(), 'messages' => array() ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$role = Role::find($id);
		$permissions = Permission::all();
		$rolePermissions = $role->perms()->lists('id');
		return view('admin.roles.edit', [ 'role' => $role, 'permissions' => $permissions, 'rolePermissions' => $rolePermissions, 'messages' => \Session::get('messages') ]);
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
		$role = Role::find($id);
		$role->name = $params['name'];
		$role->display_name = $params['display_name'];
		$role->description = $params['description'];
		$role->perms()->sync($params['permissions']);
		$role->save();
		return redirect()->back()->with('messages', ['successfully updated role']);
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
