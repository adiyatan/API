<?php

namespace App\Http\Controllers;

use App\Models\bookstubes;
use Illuminate\Http\Request;


class BookstubesController extends Controller
{
    public function index()
    {
        $dogs = bookstubes::all();

        return response()->json($dogs);
    }

    public function getById($id)
    {
        $dog = bookstubes::find($id);

        return response()->json($dog);
    }
    public function create(Request $request)
    {
        $dogs = bookstubes::create($request->all());
        return response()->json($dogs);
    }
    public function update(Request $request)
    {
        $dogs = bookstubes::find($request->id);
        $dogs->update($request->all());
        return response()->json($dogs);
    }
    public function destroy($id)
    {
        $dogs = bookstubes::find($id);
        $dogs->delete();
        return response()->json($dogs);
    }
}
