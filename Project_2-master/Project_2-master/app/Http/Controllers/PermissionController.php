<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LogHelper;

class PermissionController extends Controller
{
    /**
     * create a new instance of the class
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:permission-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Permission::all();

        return view('permissions.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('permissions.create');
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
                'name' => 'required|unique:permissions,name',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Permission Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create a permission due to validation error: ' . json_encode($e->errors()),
                'permissions'
            );
            return redirect()->back()->withErrors($e->errors());
        }

        try {
            $permission = Permission::create(['name' => $request->input('name')]);

            LogHelper::log(
                'Created Permission',
                'INFO',
                'User ' . Auth::user()->email . ' created permission: ' . $permission->name,
                'permissions',
                $permission->id
            );

            return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Permission Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create permission. Error: ' . $e->getMessage(),
                'permissions'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the permission.']);
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
        $permission = Permission::find($id);

        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permission = Permission::find($id);

        return view('permissions.edit', compact('permission'));
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
                'name' => 'required'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Permission Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update permission due to validation error: ' . json_encode($e->errors()),
                'permissions'
            );
            return redirect()->back()->withErrors($e->errors());
        }

        try {
            $permission = Permission::find($id);
            $oldName = $permission->name;
            $permission->name = $request->input('name');
            $permission->save();

            LogHelper::log(
                'Updated Permission',
                'INFO',
                'User ' . Auth::user()->email . ' updated permission from "' . $oldName . '" to "' . $permission->name . '"',
                'permissions',
                $permission->id
            );

            return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Permission Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update permission. Error: ' . $e->getMessage(),
                'permissions'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the permission.']);
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
            $permission = Permission::find($id);
            $permissionName = $permission->name;
            $permission->delete();

            LogHelper::log(
                'Deleted Permission',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted permission: ' . $permissionName,
                'permissions',
                $id
            );

            return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Permission Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete permission. Error: ' . $e->getMessage(),
                'permissions'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the permission.']);
        }
    }
}