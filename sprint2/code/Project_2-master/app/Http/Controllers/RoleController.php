<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LogHelper;

class RoleController extends Controller
{
    /**
     * create a new instance of the class
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Role::orderBy('id', 'DESC')->paginate(5);

        return view('roles.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = Permission::get();

        return view('roles.create', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|unique:roles,name',
                'permission' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Role Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to create a role but failed validation. Errors: ' . json_encode($e->errors()),
                'roles'
            );
            return redirect()->back()->withErrors($e->errors());
        }

        try {
            $role = Role::create(['name' => $request->input('name')]);
            $role->syncPermissions($request->input('permission'));

            LogHelper::log(
                'Created Role',
                'INFO',
                'User ' . Auth::user()->email . ' created role: ' . $role->name,
                'roles',
                $role->id
            );

            return redirect()->route('roles.index')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Role Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create role. Error: ' . $e->getMessage(),
                'roles'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the role.']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', 'permissions.id')
            ->where('role_has_permissions.role_id', $id)
            ->get();

        return view('roles.show', compact('role', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table('role_has_permissions')
            ->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

        return view('roles.edit', compact('role', 'permission', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'permission' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Role Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to update a role but failed validation. Errors: ' . json_encode($e->errors()),
                'roles'
            );
            return redirect()->back()->withErrors($e->errors());
        }

        try {
            $role = Role::find($id);
            $role->name = $request->input('name');
            $role->save();
            $role->syncPermissions($request->input('permission'));

            LogHelper::log(
                'Updated Role',
                'INFO',
                'User ' . Auth::user()->email . ' updated role: ' . $role->name,
                'roles',
                $role->id
            );

            return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Role Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update role. Error: ' . $e->getMessage(),
                'roles'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the role.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $role = Role::find($id);
            $roleName = $role->name;
            $role->delete();

            LogHelper::log(
                'Deleted Role',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted role: ' . $roleName,
                'roles',
                $id
            );

            return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Role Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete role. Error: ' . $e->getMessage(),
                'roles'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the role.']);
        }
    }
}