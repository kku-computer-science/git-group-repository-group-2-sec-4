<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Helpers\LogHelper;
class FundController extends Controller
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
                $funds = Fund::with('User')->get();
            } else {
                $funds = User::find($id)->fund()->get();
            }

            

            return view('funds.index', compact('funds'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Fund List View Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to view the fund list. Error: ' . $e->getMessage(),
                'funds'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the fund list.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('funds.create');
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
                'fund_name' => 'required',
                'fund_type' => 'required',
                'support_resource' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Fund Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to create a fund but failed validation. Errors: ' . json_encode($e->errors()),
                'funds'
            );

            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $user = User::find(Auth::user()->id);
            $input = $request->all();
            if ($request->fund_type == 'ทุนภายนอก') {
                $input['fund_level'] = null;
            }

            $fund = $user->fund()->Create($input);

            LogHelper::log(
                'Created Fund',
                'INFO',
                'User ' . Auth::user()->email . ' created a research fund: ' . $fund->fund_name,
                'funds',
                $fund->id
            );

            return redirect()->route('funds.index')->with('success', 'Fund created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Fund Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create a fund. Error: ' . $e->getMessage(),
                'funds'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the fund.']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Fund  $fund
     * @return \Illuminate\Http\Response
     */
    public function show(Fund $fund)
    {
        try {
            LogHelper::log(
                'Viewed Fund Details',
                'INFO',
                'User ' . Auth::user()->email . ' viewed details of fund: ' . $fund->fund_name,
                'funds',
                $fund->id
            );

            return view('funds.show', compact('fund'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Fund Detail View Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to view fund details. Error: ' . $e->getMessage(),
                'funds'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while loading the fund details.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Fund  $fund
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $fu_id = Crypt::decrypt($id);
            $fund = Fund::find($fu_id);
            $this->authorize('update', $fund);

            LogHelper::log(
                'Editing Fund',
                'INFO',
                'User ' . Auth::user()->email . ' is editing fund: ' . $fund->fund_name,
                'funds',
                $fund->id
            );

            return view('funds.edit', compact('fund'));
        } catch (DecryptException $e) {
            return abort(404, "Fund not found.");
        } catch (\Exception $e) {
            LogHelper::log(
                'Fund Edit Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to access fund edit page. Error: ' . $e->getMessage(),
                'funds'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while trying to edit the fund.']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Fund  $fund
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fund $fund)
    {
        try {
            $request->validate([
                'fund_name' => 'required',
                'fund_type' => 'required',
                'support_resource' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LogHelper::log(
                'Fund Validation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to update fund but failed validation. Errors: ' . json_encode($e->errors()),
                'funds',
                $fund->id
            );

            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        try {
            $input = $request->all();
            if ($request->fund_type == 'ทุนภายนอก') {
                $input['fund_level'] = null;
            }

            $fund->update($input);

            LogHelper::log(
                'Updated Fund',
                'INFO',
                'User ' . Auth::user()->email . ' updated fund: ' . $fund->fund_name,
                'funds',
                $fund->id
            );

            return redirect()->route('funds.index')->with('success', 'Fund updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Fund Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update fund. Error: ' . $e->getMessage(),
                'funds',
                $fund->id
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the fund.']);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Fund  $fund
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fund $fund)
    {
        try {
            $fundName = $fund->fund_name;
            $fund->delete();

            LogHelper::log(
                'Deleted Fund',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted fund: ' . $fundName,
                'funds',
                $fund->id
            );

            return redirect()->route('funds.index')->with('success', 'Fund deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Fund Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete fund. Error: ' . $e->getMessage(),
                'funds',
                $fund->id
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the fund.']);
        }
    }
}