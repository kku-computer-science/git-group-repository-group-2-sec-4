<?php

namespace App\Http\Controllers;

use App\Exports\ExportPaper;
use App\Exports\ExportUser;
use App\Exports\UsersExport;
use App\Models\Author;
use App\Models\Paper;
use App\Models\Source_data;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\ValidationException;
use App\Helpers\LogHelper;

class PaperController extends Controller
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
            if (auth()->user()->hasRole('admin') or auth()->user()->hasRole('staff')) {
                $papers = Paper::with('teacher', 'author')->orderBy('paper_yearpub', 'desc')->get();
            } else {
                $papers = Paper::with('teacher', 'author')->whereHas('teacher', function ($query) use ($id) {
                    $query->where('users.id', '=', $id);
                })->orderBy('paper_yearpub', 'desc')->get();
            }
            return view('papers.index', compact('papers'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Error Loading Research Papers',
                'ERROR',
                'An error occurred while loading research papers: ' . $e->getMessage(),
                'papers'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while loading research papers.']);
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
            $source = Source_data::all();
            $users = User::role(['teacher', 'student'])->get();
            return view('papers.create', compact('source', 'users'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Error Opening Create Research Paper Page',
                'ERROR',
                'An error occurred while opening the create research paper page: ' . $e->getMessage(),
                'papers'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while opening the page.']);
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
            $validator = Validator::make($request->all(), [
                'paper_name' => 'required|unique:papers,paper_name',
                'paper_type' => 'required',
                'paper_sourcetitle' => 'required',
                'paper_yearpub' => 'required',
                'paper_volume' => 'required',
                'paper_doi' => 'required',
            ]);

            if ($validator->fails()) {
                LogHelper::log(
                    'Research Paper Validation Failed',
                    'ERROR',
                    'User ' . Auth::user()->email . ' attempted to create a research paper but failed validation. Errors: ' . json_encode($validator->errors()),
                    'papers'
                );
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $input = $request->except(['_token']);
            $input['keyword'] = explode(', ', $input['keyword']);

            $paper = Paper::create($input);

            LogHelper::log(
                'Created Research Paper',
                'INFO',
                'User ' . Auth::user()->email . ' created research paper: ' . $paper->paper_name,
                'papers',
                $paper->id
            );

            return redirect()->route('papers.index')->with('success', 'Research paper created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Paper Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create research paper. Error: ' . $e->getMessage(),
                'papers'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while saving the research paper.']);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Paper $paper)
    {
        try {
            $paper['keyword'] = collect($paper['keyword'])->implode('$', ', ');
            // LogHelper::log(
            //     'Viewed Research Paper',
            //     'INFO',
            //     'User ' . Auth::user()->email . ' viewed research paper: ' . $paper->paper_name,
            //     'papers',
            //     $paper->id
            // );
            return view('papers.show', compact('paper'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Error Viewing Research Paper',
                'ERROR',
                'An error occurred while viewing research paper: ' . $e->getMessage(),
                'papers'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while viewing the research paper.']);
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
            $id = decrypt($id);
            $paper = Paper::findOrFail($id);
            $paper['keyword'] = collect($paper['keyword'])->implode('$', ', ');

            // ✅ เพิ่มโค้ดดึง Sources Data
            $sources = Source_data::pluck('source_name', 'source_name')->all();
            $paperSource = $paper->source->pluck('source_name', 'source_name')->all();
            $users = User::role(['teacher', 'student'])->get();

            // LogHelper::log(
            //     'Opened Research Paper Edit Page',
            //     'INFO',
            //     'User ' . Auth::user()->email . ' opened edit page for research paper: ' . $paper->paper_name,
            //     'papers',
            //     $paper->id
            // );

            return view('papers.edit', compact('paper', 'users', 'sources', 'paperSource'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Error Opening Edit Page',
                'ERROR',
                'An error occurred while opening edit page for research paper: ' . $e->getMessage(),
                'papers'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while opening the edit page.']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paper $paper)
    {
        try {
            $validator = Validator::make($request->all(), [
                'paper_type' => 'required',
                'paper_sourcetitle' => 'required',
                'paper_volume' => 'required',
                'paper_issue' => 'required',
                'paper_citation' => 'required',
                'paper_page' => 'required',
            ]);

            if ($validator->fails()) {
                LogHelper::log(
                    'Research Paper Update Validation Failed',
                    'ERROR',
                    'User ' . Auth::user()->email . ' attempted to update a research paper but failed validation. Errors: ' . json_encode($validator->errors()),
                    'papers',
                    $paper->id
                );
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $input = $request->except(['_token']);
            $input['keyword'] = explode(', ', $input['keyword']);
            $paper->update($input);

            LogHelper::log(
                'Updated Research Paper',
                'INFO',
                'User ' . Auth::user()->email . ' updated research paper: ' . $paper->paper_name,
                'papers',
                $paper->id
            );

            return redirect()->route('papers.index')->with('success', 'Research paper updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Paper Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update research paper. Error: ' . $e->getMessage(),
                'papers',
                $paper->id
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the research paper.']);
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
            $paper = Paper::findOrFail($id);
            $paper->delete();

            LogHelper::log(
                'Deleted Research Paper',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted research paper: ' . $paper->paper_name,
                'papers',
                $id
            );

            return redirect()->route('papers.index')->with('success', 'Research paper deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Research Paper Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete research paper. Error: ' . $e->getMessage(),
                'papers'
            );
            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the research paper.']);
        }
    }


    public function export(Request $request)
    {
        //$export = new ExportPaper($this->getDataForExport());

        return Excel::download(new ExportUser, 'papers.xlsx');
        //return Excel::download(new ExportPaper, 'papers.xlsx');

    }
    public function callscopus(Request $request)
    {
        try {
            $response = file_get_contents($request->url);
            $data = json_decode($response, true);

            // ✅ เช็คว่ามี authors หรือไม่
            if (!isset($data['authors'])) {
                throw new \Exception('Missing "authors" key in API response');
            }

            LogHelper::log(
                'Call Scopus API Success',
                'INFO',
                'Successfully retrieved paper data from API',
                'papers'
            );

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Call Scopus API Failed', [
                'error' => $e->getMessage(),
                'url' => $request->url,
                'user' => auth()->user()->email ?? 'guest'
            ]);

            return response()->json(['error' => 'API Call Failed: ' . $e->getMessage()], 500);
        }
    }


}
