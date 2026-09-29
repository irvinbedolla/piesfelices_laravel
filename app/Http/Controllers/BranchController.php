<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::latest()->paginate(10);
        return view('branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:branches,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Branch::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => true,
        ]);

        return redirect()->route('branches.index')->with('success', 'Sucursal registrada con éxito.');
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:branches,name,' . $branch->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $branch->update($request->only(['name', 'phone', 'address', 'status']));

        return redirect()->route('branches.index')->with('success', 'Sucursal actualizada con éxito.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Sucursal eliminada con éxito.');
    }
}