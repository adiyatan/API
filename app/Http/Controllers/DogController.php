<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use Illuminate\Http\Request;

class DogController extends Controller
{
    public function lazyDog()
    {
        $dogs = Dog::all();

        return response()->json($dogs);
    }

    public function getDogById($id)
    {
        $dog = Dog::find($id);

        if (!$dog) {
            return response()->json(['message' => 'Dog not found'], 404);
        }

        return response()->json($dog);
    }

    public function add(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|string',
            'favFood' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $dog = Dog::create($request->all());

        return response()->json($dog, 201);
    }

    public function update(Request $request, $id)
    {
        $dog = Dog::findOrFail($id);

        $request->validate([
            'image' => 'nullable|string',
            'name' => 'required|string',
            'type' => 'required|string',
            'favFood' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $dog->update($request->all());

        return response()->json($dog);
    }

    public function destroy($id)
    {
        $dog = Dog::findOrFail($id);
        $dog->delete();

        return response()->json(['message' => 'Dog deleted successfully']);
    }
}
