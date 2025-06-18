<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\City;
use App\Models\Country;
use App\Http\Resources\V2\AddressCollection;
use App\Models\Address;
use App\Http\Resources\V2\CitiesCollection;
use App\Http\Resources\V2\StatesCollection;
use App\Http\Resources\V2\CountriesCollection;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\State;

class AddressController extends Controller
{
    public function addresses(Request $request)
    {
        // return new AddressCollection(Address::where('user_id', auth()->user()->id)->get());
        if(auth()->user() != null){
            $addresses = new AddressCollection(Address::where('user_id', auth()->user()->id)->get());
            // add for every address the user name
            $user = User::find(auth()->user()->id);
            foreach ($addresses as $address) {
                $address->name = $user->name;
                $address->email = $user->email;
                $address->phone = $user->phone;
            }
            return $addresses;
        }
        else{
            $addresses = new AddressCollection(Address::where('guest_id', $request->user_id)->get());
            // add for every address the user name
            $user = Guest::where('temp_user_id', $request->user_id)->first();
            foreach ($addresses as $address) {
                if($user == null) {
                    $address->name = "";
                    $address->email = "";
                    $address->phone = "";
                } else {
                    $address->name = $user->name;
                    $address->email = $user->email;
                    $address->phone = $user->phone;
                }
            }
            return $addresses;
        }
    }

    public function createShippingAddress(Request $request)
    {
        $address = new Address;
        $address->user_id = auth()->user() != null ? auth()->user()->id : 0;
        $address->guest_id = auth()->user() == null ? $request->user_id : 0;
        $address->address = $request->address;
        $address->country_id = $request->country_id;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        // $address->postal_code = $request->postal_code;
        $address->phone = $request->phone;
        $address->bloc = $request->block;
        $address->avenue = $request->avenue;
        $address->street = $request->street;
        $address->house = $request->building;
        $address->address_type = $request->selected_type;
        $address->building_name = $request->building;
        $address->building_number = $request->building;
        $address->apt_number = $request->apt_number;
        $address->floor = $request->floor;
        $address->save();

        if(auth()->user() == null) {
            $guest =  new Guest;
            $guest->temp_user_id = $request->user_id;
            $guest->name = $request->name;
            $guest->email = $request->email;
            $guest->phone = $request->phone;
            $guest->address = $request->address;
            $guest->country_id = $request->country_id;
            $guest->state_id = $request->state_id;
            $guest->city_id = $request->city_id;
            $guest->bloc = $request->block;
            $guest->avenue = $request->avenue;
            $guest->street = $request->street;
            $guest->house = $request->building;
            $guest->save();
        }

        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been added successfully')
        ]);
    }

    public function updateShippingAddress(Request $request)
    {
        $address = Address::find($request->id);
        $address->address = $request->address;
        $address->country_id = $request->country_id;
        $address->state_id = $request->state_id;
        $address->city_id = $request->city_id;
        $address->postal_code = $request->postal_code;
        $address->phone = $request->phone;
        $address->save();

        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been updated successfully')
        ]);
    }

    public function updateShippingAddressLocation(Request $request)
    {
        $address = Address::find($request->id);
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->save();

        return response()->json([
            'result' => true,
            'message' => translate('Shipping location in map updated successfully')
        ]);
    }


    public function deleteShippingAddress($id)
    {
        // $address = Address::where('id',$id)->where('user_id',auth()->user()->id)->first();
        $address = Address::find($id);
        if($address == null) {
            return response()->json([
                'result' => false,
                'message' => translate('Address not found')
            ]);
        }
        $address->delete();
        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been deleted')
        ]);
    }

    public function makeShippingAddressDefault(Request $request)
    {
        if(auth()->user() != null){
            Address::where('user_id', auth()->user()->id)->update(['set_default' => 0]); //make all user addressed non default first
        }else{
            Address::where('guest_id', $request->user_id)->update(['set_default' => 0]); //make all user addressed non default first
        }

        $address = Address::find($request->id);
        $address->set_default = 1;
        $address->save();
        return response()->json([
            'result' => true,
            'message' => translate('Default shipping information has been updated')
        ]);
    }

    public function updateAddressInCart(Request $request)
    {
        try {
            if(auth()->user() != null){
                Cart::where('user_id', auth()->user()->id)->update(['address_id' => $request->address_id]);
            }else{
                Cart::where('temp_user_id', $request->user_id)->update(['address_id' => $request->address_id]);
            }

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'result' => false,
                'message' => translate('Could not save the address')
            ], 500);
        }
        return response()->json([
            'result' => true,
            'message' => translate('Address is saved')
        ]);


    }


    public function getShippingInCart(Request $request)
    {
        if(auth()->user() != null){
           $cart= Cart::where('user_id', auth()->user()->id)->first();
        }else{
            $cart= Cart::where('temp_user_id', $request->user_id)->first();
        }

           $address = $cart->address;
           return new AddressCollection(Address::where('id', $address->id)->get());
        //    return  new AddressCollection($address);

    }

    public function updateShippingTypeInCart(Request $request)
    {
        try {
            if(auth()->user() != null){
                $carts= Cart::where('user_id', auth()->user()->id)->get();
            }else{
                $carts= Cart::where('temp_user_id', $request->user_id)->get();
            }


           foreach ($carts as $key => $cart) {

            $cart->shipping_cost = 0;
            
           if($request->shipping_type=="pickup_point"){
            $cart->shipping_type="pickup_point";
            $cart->pickup_point=$request->shipping_id;
            $cart->carrier_id=0;
           }
           else if($request->shipping_type=="home_delivery"){
            $cart->shipping_cost = getShippingCost($carts, $key);
            $cart->shipping_type="home_delivery";
            $cart->pickup_point=0;
            $cart->carrier_id=0;
           }
           else if($request->shipping_type=="carrier_base"){
            $cart->shipping_cost = getShippingCost($carts, $key,$cart->carrier_id);
            $cart->shipping_type="carrier";
            $cart->carrier_id=$request->shipping_id;
            $cart->pickup_point=0;
           }
           $cart->save();

        }

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => translate('Could not save the address')
            ], 500);
        }
        return response()->json([
            'result' => true,
            'message' => translate('Delivery address is saved')
        ]);


    }


    public function getCities()
    {
        return new CitiesCollection(City::where('status', 1)->get());
    }

    public function getStates()
    {
        return new StatesCollection(State::where('status', 1)->get());
    }

    public function getCountries(Request $request)
    {
        $country_query = Country::where('status', 1);
        if ($request->name != "" || $request->name != null) {
             $country_query->where('name', 'like', '%' . $request->name . '%');
        }
        $countries = $country_query->get();
        
        return new CountriesCollection($countries);
    }

    public function getCitiesByState($state_id,Request $request)
    {
        $city_query = City::where('status', 1)->where('state_id',$state_id);
        if ($request->name != "" || $request->name != null) {
             $city_query->where('name', 'like', '%' . $request->name . '%');
        }
        $cities = $city_query->get();
        return new CitiesCollection($cities);
    }

    public function getStatesByCountry($country_id,Request $request)
    {
        $state_query = State::where('status', 1)->where('country_id',$country_id);
        if ($request->name != "" || $request->name != null) {
            $state_query->where('name', 'like', '%' . $request->name . '%');
       }
        $states = $state_query->get();
        return new StatesCollection($states);
    }
}
