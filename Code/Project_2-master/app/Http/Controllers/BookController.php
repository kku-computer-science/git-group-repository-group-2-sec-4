<?php

namespace App\Http\Controllers;

use App\Models\Academicwork;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogHelper;
class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = auth()->user()->id;
        //$papers=User::find($id)->paper()->latest()->paginate(5);

        //$papers = Paper::with('teacher')->get();
        /*$user = User::find($id);
        $papers = $user->paper()->get();
        return response()->json($papers);*/
        if (auth()->user()->hasRole('admin') or auth()->user()->hasRole('staff')) {
            // $books = Paper::whereHas('source', function ($query) {
            //     return $query->where('source_data_id', '=', 4);
            // })->paginate(10);
            $books = Academicwork::where('ac_type', '=', 'book')->get();
            //$books = Academicwork::paginate(10);
        } else {
            // $books = Paper::with('teacher')->whereHas('teacher', function ($query) use ($id) {
            //     $query->where('users.id', '=', $id);
            // })->whereHas('source', function ($query) {
            //     return $query->where('source_data_id', '=', 4);
            // })->paginate(10);
            $books = Academicwork::with('user')->whereHas('user', function ($query) use ($id) {
                $query->where('users.id', '=', $id);
            })->paginate(10);
        }

        // $papers = Paper::with('teacher','author')->whereHas('teacher', function($query) use($id) {
        //     $query->where('users.id', '=', $id);
        //  })->paginate(10);
        //return $books;
        //return response()->json($papers);
        return view('books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('books.create');
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
            // ตรวจสอบข้อมูล
            $validator = Validator::make($request->all(), [
                'ac_name' => 'required',
                'ac_year' => 'required',
            ]);

            if ($validator->fails()) {
                LogHelper::log(
                    'Book Validation Failed',
                    'ERROR',
                    'User ' . Auth::user()->email . ' attempted to create a book but failed validation. Errors: ' . json_encode($validator->errors()),
                    'books'
                );

                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            // สร้างข้อมูลใหม่
            $input = $request->except(['_token']);
            $input['ac_type'] = 'book';

            $acw = Academicwork::create($input);
            $id = auth()->user()->id;
            $user = User::find($id);
            $user->academicworks()->attach($acw);

            LogHelper::log(
                'Created Book',
                'INFO',
                'User ' . Auth::user()->email . ' created book: ' . $acw->ac_name,
                'books',
                $acw->id
            );

            return redirect()->route('books.index')->with('success', 'Book created successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Book Creation Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to create book. Error: ' . $e->getMessage(),
                'books'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while saving the book.']);
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
            $paper = Academicwork::findOrFail($id);
            return view('books.show', compact('paper'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Book Display Failed',
                'ERROR',
                'Failed to display book with ID: ' . $id . '. Error: ' . $e->getMessage(),
                'books'
            );
            return redirect()->route('books.index')->withErrors(['error' => 'Failed to load book details.']);
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
            $book = Academicwork::findOrFail($id);
            $this->authorize('update', $book);
            return view('books.edit', compact('book'));
        } catch (\Exception $e) {
            LogHelper::log(
                'Book Edit Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' attempted to edit book ID: ' . $id . ' but encountered an error. Error: ' . $e->getMessage(),
                'books'
            );

            return redirect()->route('books.index')->withErrors(['error' => 'Failed to load book for editing.']);
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
            $book = Academicwork::findOrFail($id);

            // ตรวจสอบข้อมูล
            $validator = Validator::make($request->all(), [
                'ac_name' => 'required',
                'ac_year' => 'required',
            ]);

            if ($validator->fails()) {
                LogHelper::log(
                    'Book Update Validation Failed',
                    'ERROR',
                    'User ' . Auth::user()->email . ' attempted to update book ID: ' . $id . ' but failed validation. Errors: ' . json_encode($validator->errors()),
                    'books'
                );

                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $input = $request->except(['_token']);
            $input['ac_type'] = 'book';
            $book->update($input);

            LogHelper::log(
                'Updated Book',
                'INFO',
                'User ' . Auth::user()->email . ' updated book: ' . $book->ac_name,
                'books',
                $book->id
            );

            return redirect()->route('books.index')->with('success', 'Book updated successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Book Update Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to update book ID: ' . $id . '. Error: ' . $e->getMessage(),
                'books'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the book.']);
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
            $book = Academicwork::findOrFail($id);
            $bookName = $book->ac_name;
            $this->authorize('delete', $book);
            $book->delete();

            LogHelper::log(
                'Deleted Book',
                'WARNING',
                'User ' . Auth::user()->email . ' deleted book: ' . $bookName,
                'books',
                $id
            );

            return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
        } catch (\Exception $e) {
            LogHelper::log(
                'Book Deletion Failed',
                'ERROR',
                'User ' . Auth::user()->email . ' failed to delete book ID: ' . $id . '. Error: ' . $e->getMessage(),
                'books'
            );

            return redirect()->back()->withErrors(['error' => 'An error occurred while deleting the book.']);
        }
    }
}
