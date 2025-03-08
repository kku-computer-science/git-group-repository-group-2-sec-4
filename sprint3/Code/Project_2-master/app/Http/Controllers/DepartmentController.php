<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:departments-list|departments-create|departments-edit|departments-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:departments-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:departments-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:departments-delete', ['only' => ['destroy']]);
        //Redirect::to('dashboard')->send();
    }

    public function index(Request $request)
    {
        try {
            $data = Department::latest()->paginate(5);
            Log::info('Department list accessed by user', [
                'user_id' => auth()->user()->id,
                'route' => request()->url()
            ]);
            return view('departments.index', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error accessing department list', [
                'error' => $e->getMessage(),
                'route' => request()->url()
            ]);
            return redirect()->back()->withErrors('Error loading department list.');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('departments.create');
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
                'department_name_th' => 'required',
                'department_name_en' => 'required',
            ]);

            $input = $request->except(['_token']);
            $department = Department::create($input);

            Log::info('Department created successfully', [
                'department_id' => $department->id,
                'department_name_th' => $department->department_name_th,
                'department_name_en' => $department->department_name_en,
                'created_by' => auth()->user()->id
            ]);

            return redirect()->route('departments.index')
                ->with('success', 'Department created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating department', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()->withErrors('Error creating department: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Department $department)
    {
        Log::info('Department details viewed', [
            'department_id' => $department->id,
            'viewed_by' => auth()->user()->id
        ]);

        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        $department = Department::find($department->id);

        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Department $department)
    {
        try {
            $this->validate($request, [
                'department_name_th' => 'required',
                'department_name_en' => 'required',
            ]);

            $oldData = $department->toArray();
            $department->update($request->all());

            Log::info('Department updated successfully', [
                'department_id' => $department->id,
                'old_data' => $oldData,
                'new_data' => $request->all(),
                'updated_by' => auth()->user()->id
            ]);

            return redirect()->route('departments.index')
                ->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating department', [
                'error' => $e->getMessage(),
                'department_id' => $department->id,
                'request_data' => $request->all()
            ]);
            return redirect()->back()->withErrors('Error updating department: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        try {
            $departmentId = $department->id;
            $departmentName = $department->department_name_th;

            $department->delete();

            Log::info('Department deleted successfully', [
                'department_id' => $departmentId,
                'department_name' => $departmentName,
                'deleted_by' => auth()->user()->id
            ]);

            return redirect()->route('departments.index')
                ->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting department', [
                'error' => $e->getMessage(),
                'department_id' => $department->id
            ]);
            return redirect()->back()->withErrors('Error deleting department: ' . $e->getMessage());
        }
    }
}
