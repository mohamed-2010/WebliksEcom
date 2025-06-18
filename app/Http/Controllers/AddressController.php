<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\City;
use App\Models\Guest;
use App\Models\State;
use Auth;
use Log;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $address = new Address;
        if($request->has('customer_id')){
            $address->user_id   = $request->customer_id;
        }
        else{
            $address->user_id   = Auth::user()->id;
        }
        $address->address       = $request->address;

        $address->address_label = $request->address_label;

        $address->country_id    = "";
        $address->state_id      = $request->state_id;
        $address->city_id       = $request->city_id;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        $address->postal_code   = "";
        $address->phone         = $request->phone;
        $address->bloc         = $request->bloc;
        $address->avenue         = $request->avenue;
        $address->street         = $request->street;
        $address->house         = $request->house;

        $address->address_type         = $request->selected_type;
        $address->building_name         = $request->building_name;
        $address->building_number         = $request->building_number;
        $address->apt_number         = $request->apt_number;
        $address->floor         = $request->floor;
        $address->save();

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
    public function edit($id)
    {
        $data['address_data'] = Address::findOrFail($id);
        $data['states'] = State::where('status', 1)->where('country_id', $data['address_data']->country_id)->get();
        $data['cities'] = City::where('status', 1)->where('state_id', $data['address_data']->state_id)->get();

        $returnHTML = view('frontend.partials.address_edit_modal', $data)->render();
        return response()->json(array('data' => $data, 'html'=>$returnHTML));
//        return ;
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
        $address = Address::findOrFail($id);

        $address->address       = $request->address;

        $address->address_label = $request->address_label;

        $address->country_id    = $request->country_id;
        $address->state_id      = $request->state_id;
        $address->city_id       = $request->city_id;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        $address->postal_code   = $request->postal_code;
        $address->phone         = $request->phone;

        $address->address_type         = $request->selected_type;
        $address->building_name         = $request->building_name;
        $address->building_number         = $request->building_number;
        $address->floor         = $request->floor;

        $address->save();

        flash(translate('Address info updated successfully'))->success();
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
        $address = Address::findOrFail($id);
        if(!$address->set_default){
            $address->delete();
            return back();
        }
        flash(translate('Default address can not be deleted'))->warning();
        return back();
    }

    public function getStates(Request $request) {
        $states = State::where('status', 1)->where('country_id', $request->country_id)->get();
        $html = '<option value="">'.translate("Select State").'</option>';
        if($request->session()->get('temp_user_id') != null) {
            $guest = Guest::where('temp_user_id', $request->session()->get('temp_user_id'))->first();
            $current_address = $guest != null ? Address::where('guest_id', $guest->id)->first() : null;
            if($current_address != null) {
                foreach ($states as $state) {
                    $selected = $current_address->state_id == $state->id ? 'selected' : '';
                    $html .= '<option value="' . $state->id . '" '.$selected.'>' . $state->get_translation('name') . '</option>';
                }
            }else{
                foreach ($states as $state) {
                    $html .= '<option value="' . $state->id . '">' . $state->get_translation('name') . '</option>';
                }
            }
        }else{
            if($request->address_id != null) {
                $current_address = Address::where('id', $request->address_id)->first();
                foreach ($states as $state) {
                    $selected = $current_address->state_id == $state->id ? 'selected' : '';
                    $html .= '<option value="' . $state->id . '" '.$selected.'>' . $state->get_translation('name') . '</option>';
                }
            }else {
                foreach ($states as $state) {
                    $html .= '<option value="' . $state->id . '">' . $state->get_translation('name') . '</option>';
                }
            }
        }

        echo json_encode($html);
    }

    public function getCities(Request $request) {
        $stateId = $request->state_id;
        if (!is_numeric($stateId)) {
            $state = State::where('name', $stateId)->first();
            if ($state) {
                $stateId = $state->id;
            } else {
                return response()->json(['error' => 'Invalid state name'], 400);
            }
        }
        $cities = City::where('status', 1)->where('state_id', $stateId)->get();
        $html = '<option value="">'.translate("Select City").'</option>';
        if($request->session()->get('temp_user_id') != null) {
            $guest = Guest::where('temp_user_id', $request->session()->get('temp_user_id'))->first();
            $current_address = $guest != null ? Address::where('guest_id', $guest->id)->first() : null;
            if($current_address != null) {
                foreach ($cities as $row) {
                    $selected = $current_address->city_id == $row->id ? 'selected' : '';
                    $html .= '<option value="' . $row->id . '" '.$selected.'>' . $row-> getTranslation('name') . '</option>';
                }
            }else{
                foreach ($cities as $row) {
                    $html .= '<option value="' . $row->id . '">' . $row->getTranslation('name') . '</option>';
                }
            }
        }else{
            foreach ($cities as $row) {
                $html .= '<option value="' . $row->id . '">' . $row->getTranslation('name') . '</option>';
            }
        }

        echo json_encode($html);
    }
    public function set_default($id){
        foreach (Auth::user()->addresses as $key => $address) {
            $address->set_default = 0;
            $address->save();
        }
        $address = Address::findOrFail($id);
        $address->set_default = 1;
        $address->save();

        return back();
    }
}
