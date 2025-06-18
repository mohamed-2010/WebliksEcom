<?php

namespace App\Http\Controllers;

use App\Models\Branche;
use App\Models\BrancheCities;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branche::paginate(10);
        return view('backend.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('backend.branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:branches,name'
        ]);

        Branche::create([
            'name' => $request->name
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully');
    }

    public function edit($id)
    {
        $branch = Branche::find($id);
        return view('backend.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branche $branch)
    {
        $request->validate([
            'name' => 'required|unique:branches,name,' . $branch->id
        ]);

        $branch->update([
            'name' => $request->name
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully');
    }       

    public function destroy($id)
    {
        $branch = Branche::find($id);
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully');
    }

    public function manage_cities($id)
    {
        $branch = Branche::find($id);
        $branch_cities = $branch->cities;
        //SELECT * FROM `states` WHERE status = 1;
        $states = State::where('status', 1)->get();
        $cities = City::where('status', 1)->get();
        return view('backend.branches.control_branch_cities', compact('branch', 'cities', 'branch_cities', 'states'));
    }

    public function add_city(Request $request, $id)
    {
        $branch = Branche::find($id);
        $old_cities = $branch->cities;
        $cities = City::where('status', 1)->get();
        $request_cities = $request->cities;

        if($request_cities)
        {
            foreach ($old_cities as $old_city) {
                if (!in_array($old_city->id, $request_cities)) {
                    $branch->cities()->detach($old_city->id);
                }
            }
    
            foreach ($request_cities as $request_city) {
                if (!$old_cities->contains($request_city)) {
                    $branch->cities()->attach($request_city);
                }
            }
        }else{
            foreach ($old_cities as $old_city) {
                $branch->cities()->detach($old_city->id);
            }
        }

        return redirect()->route('branches.index', $id)->with('success', 'City added successfully');
    }
}
