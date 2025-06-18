<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippmentController extends Controller
{
    public function create_mashkor_order($request)
    {
        $postData = array(
            "branch_id" => $request["branch_id"],
            "customer_name" => $request["customer_name"],
            "payment_type" => $request["payment_method"],
            "mobile_number" => $request["mobile_number"],
            "amount_to_collect" => $request["amount_to_collect"],
            "vendor_order_id" => $request["vendor_order_id"],
            "drop_off" => array(
                "landmark" => $request["customer_city"],
                "area" => $request["customer_area"],
                "address" => $request["customer_address"],
                'block' => $request['block'],
                'avenue' => $request['avenue'],
                'street' => $request['street'],
                'building' => $request['building'], //building is house
            )
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://private-anon-33ddfebd68-mashkororder.apiary-mock.com/v1/b/ig/order/create");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ". env('MASHKOR_AUTH_KEY'),
            "x-api-key: ". env('MASHKOR_SECRET_KEY')
        ));

        $response = json_decode(curl_exec($ch));
        return $response;
    }

    public function create_quick_delivery_order($request) {
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, "http://staging.quickdeliveryco.com/api/v1/partner/create-order");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
    
        $postData = array(
            "orderId" => $request["vendor_order_id"],
            "vendorId" => $request["vendor_order_id"],
            "dropDetails" => array(
                array(
                    "paymentMode" => $request["payment_method"],
                    "recipientName" => $request["customer_name"],
                    "recipientMobileNo" => $request["mobile_number"],
                    "recipientEmail" => $request["recipient_email"],
                    "driverNote" => $request["driver_note"],
                    "dropLocation" => $request["customer_address"],
                    "isLocationVerified" => false,
                    "governorate" => $request["customer_city"],
                    "area" => $request["customer_area"],
                    "blockNumber" => $request["block"],
                    "street" => $request["street"],
                    "avenue" => $request["avenue"],
                    "houseOrbuilding" => $request["building"],
                    "item" => array(
                        array(
                            "itemPrice" => $request["amount_to_collect"]
                        )
                    )
                )
            )
        );
    
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ". env('QUICK_SECRET_KEY')
        ));
    
        $response = json_decode(curl_exec($ch));
        return $response;
    }

    public function create_armada_delivery($request) {
        $url = 'https://api.armadadelivery.com/v0/deliveries';
    
        $data = array(
            'platformName' => 'pos',
            'platformData' => array(
                'orderId' => $request['order_id'],
                'name' => $request['customer_name'],
                'phone' => $request['mobile_number'],
                'area' => $request['customer_area'],
                'street' => $request['street'],
                //'avenue' => $request['avenue'],
                'buildingNumber' => $request['building'],
                'block' => $request['block'],
                'amount' => $request['amount_to_collect'],
                'paymentType' => $request['payment_method'] != "cash_on_delivery" ? "paid" : "cash_on_delivery"
            )
        );

        $payload = json_encode($data);

      //  return dd($payload);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Key ' . env('ARMADA_SECRET_KEY')
        ));
    
        $response = json_decode(curl_exec($ch));
        Log::info($response);
        return $response;
    }
    
    public function armada_web_hooks(Request $request) {
        Log::info($request);
    }

    private function get_mashkor_status($num) {
        switch ($num)
        {
            case 0:
                return 'New';
            case 1:
                return 'Confirmed';
            case 2:
                return 'Assigned';
            case 3:
                return 'Pickup Started';
            case 4:
                return 'Picked Up';
            case 5:
                return 'In Delivery';
            case 6:
                return 'Arrived Destination';
            case 10:
                return 'Delivered';
            case 11:
                return 'Canceled';
        }
    }

    public function getMashkorOrderDetails($id, $branch_id) {
        $ch = curl_init();
        //https://private-anon-5af3732f1b-mashkororder.apiary-mock.com/v1/b/ig/order/B-6M655?branch_id=6451ee49-7a28-4dec-bb99-9ff5f258e233
        $url = "https://private-anon-5af3732f1b-mashkororder.apiary-mock.com/v1/b/ig/order/".$id."?branch_id=".$branch_id;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ". env('MASHKOR_AUTH_KEY'),
            "x-api-key: ". env('MASHKOR_SECRET_KEY')
        ));

        $response = json_decode(curl_exec($ch));
        //return dd($response);
        if($response != null) {
            $status = $this->get_mashkor_status($response->data->status);
        }else{
            $status = 'Not Found';
        }
        return $status;
    }

    public function getQuickDeliveryOrderDetails($id) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, " http://staging.quickdeliveryco.com/api/v1/partner/orders/".$id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ". env('QUICK_SECRET_KEY'),
        ));

        $response = json_decode(curl_exec($ch));
        $count_of_status = count($response['data'][0]['orderStatus']);
        if($response != null) {
            $status = $response->result->data[0]->orderStatus[$count_of_status - 1]->status;
        }else{
            $status = 'Not Found';
        }
        return $status;
    }

    public function getArmadaOrderDetails($id) {

        $ch = curl_init();
        $url = 'http://armada-sandbox-api.herokuapp.com/v0/deliveries/'.$id;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Key ' . env('ARMADA_SECRET_KEY')
        ));

        $response = json_decode(curl_exec($ch));
        //return dd($response);
        if($response != null) {
            $status = $response->orderStatus;
        }else{
            $status = 'Not Found';
        }
        return $status;
    }
}
