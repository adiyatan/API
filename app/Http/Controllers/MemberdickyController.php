<?php

namespace App\Http\Controllers;

use App\Models\memberdicky;
use App\Http\Requests\StorememberdickyRequest;
use App\Http\Requests\UpdatememberdickyRequest;
use Illuminate\Http\Request;

class MemberdickyController extends Controller
{
    public function index()
    {
        $dogs = memberdicky::all();

        return response()->json($dogs);
    }

    public function getById($id)
    {
        $dog = memberdicky::find($id);

        return response()->json($dog);
    }
    public function create(Request $request)
    {
        $dogs = memberdicky::create($request->all());
        return response()->json($dogs);
    }
    public function update(Request $request)
    {
        $dogs = memberdicky::find($request->id);
        $dogs->update($request->all());
        return response()->json($dogs);
    }
    public function destroy($id)
    {
        $dogs = memberdicky::find($id);
        $dogs->delete();
        return response()->json($dogs);
    }
}
