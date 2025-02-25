<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ResearchProject;
use App\Models\User;
use Illuminate\Http\Request;
use SebastianBergmann\Environment\Console;
use Illuminate\Support\Facades\Log;
use App\Models\Fund;
use App\Models\Outsider;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;

class ResearchProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $id = auth()->user()->id;
            if (auth()->user()->HasRole('admin') || auth()->user()->HasRole('headproject') || auth()->user()->HasRole('staff')) {
                $researchProjects = ResearchProject::with('User')->get();
            } else {
                $researchProjects = User::find($id)->researchProject()->get();
            }

            LogHelper::log(
                'Viewed Research Projects List',
                'INFO',
                'User ' . Auth::user()->email . ' viewed the research projects list.',
                'research_projects'
            );

            return view('research_projects.index', compact('researchProjects'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Projects List View Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to view research projects list. Error: ' . $e->getMessage(),
                'research_projects'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the research projects list.']);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::role(['teacher', 'student'])->get();
        $funds = Fund::get();
        $deps = Department::get();
        return view('research_projects.create', compact('users', 'funds', 'deps'));
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
            $request->validate([
                'project_name' => 'required',
                'budget' => 'required|numeric',
                'project_year' => 'required',
                'fund' => 'required',
                'head' => 'required'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Research Project Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to create a research project but failed validation. Errors: ' . json_encode($e->errors()),
                'research_projects'
            );

            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $fund = Fund::find($request->fund);
            $researchProject = $fund->researchProject()->Create($request->all());

            LogHelper::log(
                'Created Research Project',
                'INFO',
                'User ' . Auth::user()->email . ' created a research project: ' . $researchProject->project_name,
                'research_projects',
                $researchProject->id
            );

            $researchProject->user()->attach($request->head, ['role' => 1]);

            if (isset($request->moreFields)) {
                foreach ($request->moreFields as $value) {
                    if ($value['userid'] != null) {
                        $researchProject->user()->attach($value, ['role' => 2]);
                    }
                }
            }

            return redirect()->route('researchProjects.index')->with('success', 'Research Project created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Project Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create research project. Error: ' . $e->getMessage(),
                'research_projects'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the research project.']);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ResearchProject  $researchProject
     * @return \Illuminate\Http\Response
     */
    public function show(ResearchProject $researchProject)
    {
        try {
            LogHelper::log(
                'Viewed Research Project Details',
                'INFO',
                'User ' . Auth::user()->email . ' viewed research project: ' . $researchProject->project_name,
                'research_projects',
                $researchProject->id
            );

            return view('research_projects.show', compact('researchProject'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Project Detail View Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to view research project details. Error: ' . $e->getMessage(),
                'research_projects'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the research project details.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ResearchProject  $researchProject
     * @return \Illuminate\Http\Response
     */
    public function edit(ResearchProject $researchProject)
    {
        try {
            $this->authorize('update', $researchProject);

            LogHelper::log(
                'Editing Research Project',
                'INFO',
                'User ' . Auth::user()->email . ' is editing research project: ' . $researchProject->project_name,
                'research_projects',
                $researchProject->id
            );

            $users = User::role(['teacher', 'student'])->get();
            $funds = Fund::get();
            $deps = Department::get();
            return view('research_projects.edit', compact('researchProject', 'users', 'funds', 'deps'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Project Edit Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to access research project edit page. Error: ' . $e->getMessage(),
                'research_projects'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while trying to edit the research project.']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ResearchProject  $researchProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResearchProject $researchProject)
    {
        try {
            $request->validate([
                'project_name' => 'required',
                'budget' => 'required|numeric',
                'project_year' => 'required',
                'fund' => 'required',
                'head' => 'required'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Research Project Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to update research project but failed validation. Errors: ' . json_encode($e->errors()),
                'research_projects',
                $researchProject->id
            );

            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $this->authorize('update', $researchProject);
            $researchProject->update($request->all());

            LogHelper::log(
                'Updated Research Project',
                'INFO',
                'User ' . Auth::user()->email . ' updated research project: ' . $researchProject->project_name,
                'research_projects',
                $researchProject->id
            );

            $researchProject->user()->sync([$request->head => ['role' => 1]]);

            return redirect()->route('researchProjects.index')->with('success', 'Research Project updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Project Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update research project. Error: ' . $e->getMessage(),
                'research_projects',
                $researchProject->id
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the research project.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ResearchProject  $researchProject
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResearchProject $researchProject)
    {
        try {
            $researchProject->delete();

            LogHelper::log(
                'Deleted Research Project',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted research project: ' . $researchProject->project_name,
                'research_projects',
                $researchProject->id
            );

            return redirect()->route('researchProjects.index')->with('success', 'Research Project deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Project Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete research project. Error: ' . $e->getMessage(),
                'research_projects',
                $researchProject->id
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the research project.']);
        }
    }
}