<?php

namespace App\Http\Controllers;

use App\Models\memberTubes;
use App\Http\Requests\StorememberTubesRequest;
use App\Http\Requests\UpdatememberTubesRequest;
use Illuminate\Http\Request;

class MemberTubesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dogs = memberTubes::all();

        return response()->json($dogs);
    }

    public function getById($id)
    {
        $dog = memberTubes::find($id);

        return response()->json($dog);
    }
    public function create(Request $request)
    {
        $dogs = memberTubes::create($request->all());
        return response()->json($dogs);
    }
    public function update(Request $request)
    {
        $dogs = memberTubes::find($request->id);
        $dogs->update($request->all());
        return response()->json($dogs);
    }
    public function destroy($id)
    {
        $dogs = memberTubes::find($id);
        $dogs->delete();
        return response()->json($dogs);
    }
}
