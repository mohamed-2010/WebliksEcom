<div class="shadow-sm bg-white p-4 rounded mb-4">
    <h1 class="heading-6 mb-0">{{translate('Select Nearest Pick-up Point')}}</h1>
</div>
@php
    $admin_products = array();
    $seller_products = array();
    // foreach (Session::get('cart') as $key => $cartItem){
    //     if(\App\Models\Product::find($cartItem['id'])->added_by == 'admin'){
    //         array_push($admin_products, $cartItem['id']);
    //     }
    //     else{
    //         $product_ids = array();
    //         if(isset($seller_products[\App\Models\Product::find($cartItem['id'])->user_id])){
    //             $product_ids = $seller_products[\App\Models\Product::find($cartItem['id'])->user_id];
    //         }
    //         array_push($product_ids, $cartItem['id']);
    //         $seller_products[\App\Models\Product::find($cartItem['id'])->user_id] = $product_ids;
    //     }
    // }
    $pickUpPoints = \App\Models\PickupPoint::all();
    // dd($pickUpPoints);
@endphp
@if ($pickUpPoints)
    <div class="row gutters-5 m-4">
        @foreach ($pickUpPoints as $key => $pick_up_point)
        <div class="col-md-6 mb-3">
            <label class="aiz-megabox d-block bg-white mb-0">
                <input type="radio" name="pickup_point_id" value="{{ $pick_up_point->id }}">
                <span class="d-flex p-3 aiz-megabox-elem">
                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                    <span class="flex-grow-1 pl-3 text-left">
                        <br><strong>{{ translate('Address') }}: {{ $pick_up_point->getTranslation('name') }}</strong>
                        <br><strong>{{ translate('Address') }}: {{ $pick_up_point->getTranslation('address') }}</strong>
                        <br><strong>{{ translate('Phone') }}: {{ $pick_up_point->phone }}</strong>
                    </span>
                </span>
            </label>
        </div>
        @endforeach
    </div>
@endif
@if (!empty($seller_products))
    @foreach ($seller_products as $key => $seller_product)
        @foreach ($seller_product as $key => $value)
            {{ $value }}<br>
        @endforeach
    @endforeach
@endif
