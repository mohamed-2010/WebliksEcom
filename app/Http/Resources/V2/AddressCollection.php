<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AddressCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {

                $location_available = false;
                $lat = 90.99;
                $lang = 180.99;

                if($data->latitude || $data->longitude) {
                    $location_available = true;
                    $lat = floatval($data->latitude) ;
                    $lang = floatval($data->longitude);
                }

                return [
                    'id'      =>(int) $data->id,
                    'user_id' =>(int) $data->user_id,
                    'address' => $data->address,
                    'country_id' => (int)  $data->country_id,
                    'state_id' =>  (int) $data->state_id,
                    'city_id' =>  (int) $data->city_id,                    
                    'country_name' => $data->country->name,
                    'state_name' => $data->state->name,
                    'city_name' => $data->city->name,
                    'postal_code' => $data->postal_code,
                    'phone' => $data->phone,
                    'set_default' =>(int) $data->set_default,
                    'location_available' => $location_available,
                    'lat' => $lat,
                    'lang' => $lang,
                    'bloc' => $data->bloc,
                    'avenue' => $data->avenue,
                    'address_label' => $data->address_label,
                    'street' => $data->street,
                    'building_name' => $data->building_name,
                    'house' => $data->house,
                    'address_type' => $data->address_type,
                    'building_number' => $data->building_number,
                    'apt_number' => $data->apt_number,
                    'floor' => $data->floor,
                    'name' => $data->name,
                    'email' => $data->email,
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
