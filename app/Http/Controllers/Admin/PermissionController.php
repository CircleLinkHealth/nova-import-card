<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Permission;
use App\Role;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

class PermissionController extends Controller
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
        $roles = Role::all();

        return view('admin.permissions.create', ['roles' => $roles, 'errors' => [], 'messages' => []]);
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
        $permission      = Permission::find($id);
        $roles           = Role::all();
        $permissionRoles = $permission->roles()->pluck('id')->all();

        return view('admin.permissions.edit', ['permission' => $permission, 'roles' => $roles, 'permissionRoles' => $permissionRoles, 'messages' => \Session::get('messages')]);
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
        $permissions = Permission::paginate(10);

        return view('admin.permissions.index', ['permissions' => $permissions]);
    }

    /**
     * Creates Excel file that shows which roles have which permissions.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function makeRoleExcel()
    {
        $today = Carbon::now();
        $perms = Permission::with('roles')->get();
        $roles = Role::get();

        $columns = [];
        foreach ($roles as $role) {
            $columns[] = $role->display_name;
        }
        $roles = collect($columns);

        $rows = [];
        foreach ($perms as $perm) {
            $row               = [];
            $row['Permission'] = $perm->display_name;
            foreach ($roles as $role) {
                $input = ' ';
                if ($perm->roles->where('display_name', $role)->count() > 0) {
                    $input = 'X';
                }
                $row[$role] = $input;
            }
            $rows[] = $row;
        }

        $report = Excel::create("Roles-Permissions Chart for {$today->toDateString()}", function ($excel) use ($rows) {
            $excel->sheet('Rules-Permissions', function ($sheet) use ($rows) {
                $sheet->fromArray($rows);
            });
        })
            ->store('xls', false, true);
        $excel = auth()->user()
            ->saasAccount
            ->addMedia($report['full'])
            ->toMediaCollection("excel_report_for_roles_permissions{$today->toDateString()}");

        return $this->downloadMedia($excel);
    }

    /**
     * Creates Excel file that shows all middleware(permissions included)
     * that each route in the system uses.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function makeRouteExcel()
    {
        $today      = Carbon::now();
        $collection = Route::getRoutes();
        $allRoutes  = collect($collection->getRoutesByName());

        $routes = [];

        foreach ($allRoutes as $route) {
            $middleware = implode(', ', $route->gatherMiddleware());
            $routes[]   = [
                'Route(uri)' => $route->uri(),
                'Middleware' => $middleware,
            ];
        }

        $report = Excel::create("Route-Permissions Chart for {$today->toDateString()}", function ($excel) use ($routes) {
            $excel->sheet('Routes-Permissions', function ($sheet) use ($routes) {
                $sheet->fromArray($routes);
            });
        })
            ->store('xls', false, true);
        $excel = auth()->user()
            ->saasAccount
            ->addMedia($report['full'])
            ->toMediaCollection("excel_report_for_routes_permissions{$today->toDateString()}");

        return $this->downloadMedia($excel);
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
        $permission = Permission::find($id);

        return view('admin.permissions.show', ['permission' => $permission, 'errors' => [], 'messages' => []]);
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
        $params                   = $request->input();
        $permission               = new Permission();
        $permission->name         = $params['name'];
        $permission->display_name = $params['display_name'];
        $permission->description  = $params['description'];
        $permission->save();
        if (isset($params['roles'])) {
            $permission->roles()->sync($params['roles']);
        }
        $permission->save();
        redirect()->route('admin.permissions.edit', [$permission->id])->with(
            'messages',
            ['successfully added new permission - '.$params['name']]
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
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $params                   = $request->input();
        $permission               = Permission::find($id);
        $permission->name         = $params['name'];
        $permission->display_name = $params['display_name'];
        $permission->description  = $params['description'];
        if (isset($params['roles'])) {
            $permission->roles()->sync($params['roles']);
        }
        $permission->save();

        return redirect()->back()->with('messages', ['successfully updated permission']);
    }
}
