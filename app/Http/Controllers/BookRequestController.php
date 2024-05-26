<?php

namespace App\Http\Controllers;

use App\Models\bookRequest;
use Illuminate\Http\Request;


class BookRequestController extends Controller
{
    public function index()
    {
        $bookRequest = bookRequest::all();

        return response()->json($bookRequest);
    }

    public function getById($id)
    {
        $dog = bookRequest::find($id);

        return response()->json($dog);
    }
    public function create(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:member_tubes,id',
            'book_id' => 'required|exists:bookstubes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:on loan,returned,lost,damaged',
        ]);

        $bookRequest = BookRequest::create($request->all());

        return response()->json($bookRequest, 201);
    }
    public function update(Request $request)
    {
        $bookRequest = bookRequest::find($request->id);
        $request->validate([
            'member_id' => 'required|exists:member_tubes,id',
            'book_id' => 'required|exists:bookstubes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:on loan,returned,lost,damaged',
        ]);

        $bookRequest->update($request->all());

        return response()->json($bookRequest, 200);
    }
    public function destroy($id)
    {
        $bookRequest = bookRequest::find($id);
        $bookRequest->delete();
        return response()->json($bookRequest);
    }
}
