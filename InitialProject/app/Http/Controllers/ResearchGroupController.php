<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ResearchGroup;
use Illuminate\Http\Request;
use App\Models\Fund;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;

class ResearchGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:groups-list|groups-create|groups-edit|groups-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:groups-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:groups-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:groups-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try {
            $researchGroups = ResearchGroup::with('User')->get();

            LogHelper::log(
                'Viewed Research Groups List',
                'INFO',
                'User ' . Auth::user()->email . ' viewed the research groups list.',
                'research_groups'
            );

            return view('research_groups.index', compact('researchGroups'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Groups List View Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to view research groups list. Error: ' . $e->getMessage(),
                'research_groups'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the research groups list.']);
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
        return view('research_groups.create', compact('users', 'funds'));
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
                'group_name_th' => 'required',
                'group_name_en' => 'required',
                'head' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Research Group Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to create a research group but failed validation. Errors: ' . json_encode($e->errors()),
                'research_groups'
            );

            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $input = $request->all();
            if ($request->group_image) {
                $input['group_image'] = time() . '.' . $request->group_image->extension();
                $request->group_image->move(public_path('img'), $input['group_image']);
            }

            $researchGroup = ResearchGroup::create($input);
            $researchGroup->user()->attach($request->head, ['role' => 1]);

            if ($request->moreFields) {
                foreach ($request->moreFields as $value) {
                    if ($value['userid'] != null) {
                        $researchGroup->user()->attach($value, ['role' => 2]);
                    }
                }
            }

            LogHelper::log(
                'Created Research Group',
                'INFO',
                'User ' . Auth::user()->email . ' created a research group: ' . $researchGroup->group_name_th,
                'research_groups',
                $researchGroup->id
            );

            return redirect()->route('researchGroups.index')->with('success', 'Research group created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Group Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create research group. Error: ' . $e->getMessage(),
                'research_groups'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the research group.']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Fund  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function show(ResearchGroup $researchGroup)
    {
        try {
            LogHelper::log(
                'Viewed Research Group Details',
                'INFO',
                'User ' . Auth::user()->email . ' viewed research group: ' . $researchGroup->group_name_th,
                'research_groups',
                $researchGroup->id
            );

            return view('research_groups.show', compact('researchGroup'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Group Detail View Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to view research group details. Error: ' . $e->getMessage(),
                'research_groups'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the research group details.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Fund  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(ResearchGroup $researchGroup)
    {
        try {
            $this->authorize('update', $researchGroup);

            LogHelper::log(
                'Editing Research Group',
                'INFO',
                'User ' . Auth::user()->email . ' is editing research group: ' . $researchGroup->group_name_th,
                'research_groups',
                $researchGroup->id
            );

            $users = User::get();
            return view('research_groups.edit', compact('researchGroup', 'users'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Group Edit Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to access research group edit page. Error: ' . $e->getMessage(),
                'research_groups'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while trying to edit the research group.']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ResearchGroup  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResearchGroup $researchGroup)
    {
        try {
            $request->validate([
                'group_name_th' => 'required',
                'group_name_en' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Research Group Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to update research group but failed validation. Errors: ' . json_encode($e->errors()),
                'research_groups',
                $researchGroup->id
            );

            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $input = $request->all();
            if ($request->group_image) {
                $input['group_image'] = time() . '.' . $request->group_image->extension();
                $request->group_image->move(public_path('img'), $input['group_image']);
            }

            $researchGroup->update($input);
            $researchGroup->user()->sync([$request->head => ['role' => 1]]);

            if ($request->moreFields) {
                foreach ($request->moreFields as $value) {
                    if ($value['userid'] != null) {
                        $researchGroup->user()->attach($value, ['role' => 2]);
                    }
                }
            }

            LogHelper::log(
                'Updated Research Group',
                'INFO',
                'User ' . Auth::user()->email . ' updated research group: ' . $researchGroup->group_name_th,
                'research_groups',
                $researchGroup->id
            );

            return redirect()->route('researchGroups.index')->with('success', 'Research group updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Group Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update research group. Error: ' . $e->getMessage(),
                'research_groups',
                $researchGroup->id
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the research group.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Fund  $researchGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResearchGroup $researchGroup)
    {
        try {
            $researchGroup->delete();

            LogHelper::log(
                'Deleted Research Group',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted research group: ' . $researchGroup->group_name_th,
                'research_groups',
                $researchGroup->id
            );

            return redirect()->route('researchGroups.index')->with('success', 'Research group deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Group Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete research group. Error: ' . $e->getMessage(),
                'research_groups',
                $researchGroup->id
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the research group.']);
        }
    }
}
