<?php

namespace App\Http\Controllers;

use App\Models\Academicwork;
use App\Models\Author;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;

class PatentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = auth()->user()->id;
        try {
            if (auth()->user()->hasRole('admin') or auth()->user()->hasRole('staff')) {
                $patents = Academicwork::where('ac_type', '!=', 'book')->get();
            } else {
                $patents = Academicwork::with('user')->where('ac_type', '!=', 'book')->whereHas('user', function ($query) use ($id) {
                    $query->where('users.id', '=', $id);
                })->paginate(10);
            }
            return view('patents.index', compact('patents'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Patent Index Error',
                'ERROR',
                'Failed to load patents. Error: ' . $e->getMessage(),
                'patents'
            );
            return redirect()->back()->withErrors(['error' => 'Error loading patents list.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $users = User::role(['teacher', 'student'])->get();
            return view('patents.create', compact('users'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Patent Create Page Error',
                'ERROR',
                'Failed to load create patent page. Error: ' . $e->getMessage(),
                'patents'
            );
            return redirect()->back()->withErrors(['error' => 'Error loading create patent page.']);
        }
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
                'ac_name' => 'required',
                'ac_type' => 'required',
                'ac_year' => 'required',
                'ac_refnumber' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Patent Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create a patent due to validation errors. Errors: ' . json_encode($e->errors()),
                'patents'
            );
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $input = $request->except(['_token']);
            $patent = Academicwork::create($input);

            LogHelper::log(
                'Patent Created',
                'INFO',
                'User ' . Auth::user()->email . ' created a new patent: ' . $patent->ac_name,
                'patents',
                $patent->id
            );

            return redirect()->route('patents.index')->with('success', 'Patent created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Patent Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create a patent. Error: ' . $e->getMessage(),
                'patents'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the patent.']);
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
        try {
            $patent = Academicwork::findOrFail($id);
            return view('patents.show', compact('patent'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Patent Show Error',
                'ERROR',
                'Failed to load patent details for ID: ' . $id . '. Error: ' . $e->getMessage(),
                'patents'
            );
            return redirect()->back()->withErrors(['error' => 'Error loading patent details.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $patent = Academicwork::findOrFail($id);
            $this->authorize('update', $patent);
            $users = User::role(['teacher', 'student'])->get();
            return view('patents.edit', compact('patent', 'users'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Patent Edit Error',
                'ERROR',
                'Failed to load edit patent page for ID: ' . $id . '. Error: ' . $e->getMessage(),
                'patents'
            );
            return redirect()->back()->withErrors(['error' => 'Error loading edit patent page.']);
        }
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
            $patent = Academicwork::findOrFail($id);

            $this->validate($request, [
                'ac_name' => 'required',
                'ac_type' => 'required',
                'ac_year' => 'required',
                'ac_refnumber' => 'required',
            ]);

            $patent->update([
                'ac_name' => $request->ac_name,
                'ac_type' => $request->ac_type,
                'ac_year' => $request->ac_year,
                'ac_refnumber' => $request->ac_refnumber,
            ]);

            LogHelper::log(
                'Patent Updated',
                'INFO',
                'User ' . Auth::user()->email . ' updated patent: ' . $patent->ac_name,
                'patents',
                $patent->id
            );

            return redirect()->route('patents.index')->with('success', 'Patent updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Patent Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update patent ID: ' . $id . '. Error: ' . $e->getMessage(),
                'patents'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the patent.']);
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
            $patent = Academicwork::findOrFail($id);
            $patentName = $patent->ac_name;
            $patent->delete();

            LogHelper::log(
                'Patent Deleted',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted patent: ' . $patentName,
                'patents',
                $id
            );

            return redirect()->route('patents.index')->with('success', 'Patent deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Patent Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete patent ID: ' . $id . '. Error: ' . $e->getMessage(),
                'patents'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the patent.']);
        }
    }
}
