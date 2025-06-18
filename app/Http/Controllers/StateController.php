<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\State;
use App\Models\Country;
use App\Models\StateTranslation;

class StateController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:manage_shipping_states'])->only('index','edit');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_country = $request->sort_country;
        $sort_state = $request->sort_state;

        $lang = $request->lang ?? env('DEFAULT_LANGUAGE');

        $state_queries = State::query();
        if ($request->sort_state) {
            $state_queries->where('name', 'like', "%$sort_state%");
        }
        if ($request->sort_country) {
            $state_queries->where('country_id', $request->sort_country);
        }

        $states = $state_queries->orderBy('status', 'desc')->paginate(15);
        return view('backend.setup_configurations.states.index', compact('states', 'sort_country', 'sort_state', 'lang'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $state = new State;

        $state->name        = $request->name[0];
        $state->country_id  = $request->country_id;

        $state->save();

        foreach (\App\Models\Language::all() as $key => $language) {
            $state_translation = StateTranslation::firstOrNew(['lang' => $language->code, 'states_id' => $state->id]);
            $state_translation->name = $request->name[$key];
            $state_translation->save();
        }

        flash(translate('State has been inserted successfully'))->success();
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $state  = State::findOrFail($id);
        $countries = Country::where('status', 1)->get();

        $lang = $request->lang ?? env('DEFAULT_LANGUAGE');

        return view('backend.setup_configurations.states.edit', compact('countries', 'state', 'lang'));
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
        $state = State::findOrFail($id);

        if($request->lang == env("DEFAULT_LANGUAGE")){
            $state->name        = $request->name;
        }
        $state->country_id  = $request->country_id;

        $state->save();

        $state_translation = StateTranslation::firstOrNew(['lang' => $request->lang, 'states_id' => $state->id]);
        $state_translation->name = $request->name;
        $state_translation->save();

        flash(translate('State has been updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        State::destroy($id);

        foreach (StateTranslation::where('states_id', $id)->get() as $key => $state_translation) {
            $state_translation->delete();
        }

        flash(translate('State has been deleted successfully'))->success();
        return redirect()->route('states.index');
    }

    public function updateStatus(Request $request)
    {
        $state = State::findOrFail($request->id);
        $state->status = $request->status;
        $state->save();

        if ($state->status) {
            foreach ($state->cities as $city) {
                $city->status = 1;
                $city->save();
            }
        }

        return 1;
    }
}
