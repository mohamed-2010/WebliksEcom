@extends('frontend.layouts.app')

@section('content')

<style>
    .selected{
        border: 1px solid #007bff !important;
        background-color: var(--hov-primary) !important;
        color: white;
    }
</style>

<section class="pt-5 mb-4">
    <div class="container">
        <div class="row">
            @php
                $delivery_info_step_active = get_setting('delivery_info_step_status');
            @endphp
            <div class="col-xl-8 mx-auto">
                <div class="row aiz-steps arrow-divider">
                    <div class="col done">
                        <div class="text-center text-success">
                            <i class="la-3x mb-2 las la-shopping-cart"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ translate('1. My Cart')}}</h3>
                        </div>
                    </div>
                    <div class="col active">
                        <div class="text-center text-primary">
                            <i class="la-3x mb-2 las la-map"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ translate('2. Shipping info')}}</h3>
                        </div>
                    </div>
                    @if($delivery_info_step_active == true)
                        <div class="col">
                            <div class="text-center">
                                <i class="la-3x mb-2 opacity-50 las la-truck"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ translate('3. Delivery info')}}</h3>
                            </div>
                        </div>
                    @endif
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-credit-card"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ $delivery_info_step_active == true ? translate('4. Payment') : translate('3. Payment') }}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">{{ $delivery_info_step_active == true ? translate('5. Confirmation') : translate('4. Confirmation') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-4 gry-bg">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-xxl-8 col-xl-10 mx-auto">
                <form class="form-default" data-toggle="validator" action="{{ route('checkout.store_delivery_info') }}" role="form" method="POST" id="checkout-form">
                    @csrf
                    @if(Auth::check())
                        {{--<div class="d-flex row pt-4">
                            options of delivery pickup or delivery
                            <!-- delivery button and selected by default -->
                            <div class="col-md-6 mx-auto mb-3" >
                                <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center selected" onclick="select_delivery_type('delivery')" id="delivery">
                                    <div class="alpha-7">{{ translate('Delivery') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 mx-auto mb-3" >
                                <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center" onclick="select_delivery_type('pickup')" id="pickup">
                                    <div class="alpha-7">{{ translate('Pickup') }}</div>
                                </div>
                            </div>
                        </div>--}}
                        @php
                            $admin_products = array();
                            $seller_products = array();
                            foreach ($carts as $key => $cartItem){
                                $product = \App\Models\Product::find($cartItem['product_id']);

                                if($product->added_by == 'admin'){
                                    array_push($admin_products, $cartItem['product_id']);
                                }
                                else{
                                    $product_ids = array();
                                    if(isset($seller_products[$product->user_id])){
                                        $product_ids = $seller_products[$product->user_id];
                                    }
                                    array_push($product_ids, $cartItem['product_id']);
                                    $seller_products[$product->user_id] = $product_ids;
                                }
                            }
                            
                            $pickup_point_list = array();
                            if (get_setting('pickup_point') == 1) {
                                $pickup_point_list = \App\Models\PickupPoint::where('pick_up_status',1)->get();
                            }
                        @endphp
                        @if (!empty($admin_products))
                            @php
                                $physical = false;
                            @endphp
                            @foreach ($admin_products as $key => $cartItem)
                                @php
                                    $product = \App\Models\Product::find($cartItem);
                                    if ($product->digital == 0) {
                                        $physical = true;
                                    }
                                @endphp
                            @endforeach
                            @if ($physical)
                                <div class="row pt-4">
                                    <div class="col-md-12">
                                        <div class="row gutters-5">
                                            @if (get_setting('shipping_type') != 'carrier_wise_shipping')
                                            <div class="col-6">
                                                <label class="aiz-megabox d-block bg-white mb-0">
                                                    <input
                                                        type="radio"
                                                        name="shipping_type_{{ \App\Models\User::where('user_type', 'admin')->first()->id }}"
                                                        value="home_delivery"
                                                        onchange="show_pickup_point(this, 'admin')"
                                                        data-target=".pickup_point_id_admin"
                                                        checked
                                                    >
                                                    <span class="d-flex p-3 aiz-megabox-elem">
                                                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                        <span class="flex-grow-1 pl-3 fw-600">{{  translate('Home Delivery') }}</span>
                                                    </span>
                                                </label>
                                            </div>
                                            @else
                                            <div class="col-6">
                                                <label class="aiz-megabox d-block bg-white mb-0">
                                                    <input
                                                        type="radio"
                                                        name="shipping_type_{{ \App\Models\User::where('user_type', 'admin')->first()->id }}"
                                                        value="carrier"
                                                        onchange="show_pickup_point(this, 'admin')"
                                                        data-target=".pickup_point_id_admin"
                                                        checked
                                                    >
                                                    <span class="d-flex p-3 aiz-megabox-elem">
                                                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                        <span class="flex-grow-1 pl-3 fw-600">{{  translate('Carrier') }}</span>
                                                    </span>
                                                </label>
                                            </div>
                                            @endif

                                            @if ($pickup_point_list && count($pickup_point_list) > 0)
                                            <div class="col-6">
                                                <label class="aiz-megabox d-block bg-white mb-0">
                                                    <input
                                                        type="radio"
                                                        name="shipping_type_{{ \App\Models\User::where('user_type', 'admin')->first()->id }}"
                                                        value="pickup_point"
                                                        onchange="show_pickup_point(this, 'admin')"
                                                        data-target=".pickup_point_id_admin"
                                                    >
                                                    <span class="d-flex p-3 aiz-megabox-elem">
                                                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                        <span class="flex-grow-1 pl-3 fw-600">{{  translate('Local Pickup') }}</span>
                                                    </span>
                                                </label>
                                            </div>
                                            @endif
                                            
                                        </div>
                                        @if ($pickup_point_list)
                                        <div class="mt-4 pickup_point_id_admin d-none">
                                            <select
                                                class="form-control aiz-selectpicker"
                                                name="pickup_point_id_{{ \App\Models\User::where('user_type', 'admin')->first()->id }}"
                                                data-live-search="true"
                                            >
                                                    <option>{{ translate('Select your nearest pickup point')}}</option>
                                                @foreach ($pickup_point_list as $pick_up_point)
                                                    <option
                                                        value="{{ $pick_up_point->id }}"
                                                        data-content="<span class='d-block'>
                                                                        <span class='d-block fs-16 fw-600 mb-2'>{{ $pick_up_point->getTranslation('name') }}</span>
                                                                        <span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pick_up_point->getTranslation('address') }}</span>
                                                                        <span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pick_up_point->phone }}</span>
                                                                    </span>"
                                                    >
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                        <div class="shadow-sm bg-white p-4 rounded mb-4" id="delivery_info">
                            <h1>Address</h1>
                            <div class="row gutters-5">
                                @foreach (Auth::user()->addresses as $key => $address)
                                    <div class="col-md-6 mb-3">
                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" id="address_id_{{ $key }}" name="address_id" value="{{ $address->id }}">
                                            <span class="d-flex p-3 aiz-megabox-elem">
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                <span class="flex-grow-1 pl-3 text-left">
                                                    {{-- <div>
                                                        <span class="opacity-60">{{ translate('Address') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->address }}</span>
                                                    </div> --}}
                                                    <div>
                                                        <span class="opacity-60">{{ translate('State') }}:</span>
                                                        <span class="fw-600 ml-2">{{ optional($address->state)->name }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('City') }}:</span>
                                                        <span class="fw-600 ml-2">{{ optional($address->city)->name }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Bloc') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->bloc }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Avenue') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->avenue }}</span>
                                                    </div>
                                                    {{-- <div>
                                                        <span class="opacity-60">{{ translate('Street') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->street }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('House') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->house }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Phone') }}:</span>
                                                        <span class="fw-600 ml-2">{{ $address->phone }}</span>
                                                    </div> --}}
                                                </span>
                                            </span>
                                        </label>
                                        <div class="dropdown position-absolute right-0 top-0">
                                            <button class="btn bg-gray px-2" type="button" data-toggle="dropdown">
                                                <i class="la la-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" onclick="edit_address('{{$address->id}}')">
                                                    {{ translate('Edit') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <input type="hidden" name="checkout_type" value="logged">
                                <div class="col-md-6 mx-auto mb-3" >
                                    <div class="border p-3 rounded mb-3 c-pointer text-center bg-white h-100 d-flex flex-column justify-content-center" onclick="add_new_address()">
                                        <i class="las la-plus la-2x mb-3"></i>
                                        <div class="alpha-7">{{ translate('Add New Address') }}</div>
                                    </div>
                                </div>
                            </div>
                            {{--<h1>Shipping</h1>
                            <div class="row gutters-5">
                                @if (get_setting('mashkor_status') == 1)
                                    <div class="col-md-6 mb-3">
                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" name="shipping_company" value="mashkor" checked required>
                                            <input type="hidden" name="shipping_cost" value="{{env('MASHKOR_PRICE')}}">
                                            <span class="d-flex p-3 aiz-megabox-elem">
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                <span class="flex-grow-1 pl-3 text-left">
                                                    <div class="clearfix">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="150" height="30" viewBox="0 0 279.365 62.746">
                                                            <g id="Group_703" data-name="Group 703" transform="translate(-125.225 -94.91)">
                                                                <g id="Group_2" data-name="Group 2" transform="translate(125.225 94.91)">
                                                                    <path id="Path_5" data-name="Path 5" d="M53.388-2020.594a6.491,6.491,0,0,0,0,9.177,6.486,6.486,0,0,0,9.177,0,6.485,6.485,0,0,0,0-9.177A6.486,6.486,0,0,0,53.388-2020.594Z" transform="translate(-51.488 2056.819)" fill="currentColor"></path>
                                                                    <path id="Path_6" data-name="Path 6" d="M74.825-2023.624a6.491,6.491,0,0,0,0-9.177,6.49,6.49,0,0,0-9.18,0,6.491,6.491,0,0,0,0,9.177A6.488,6.488,0,0,0,74.825-2023.624Z" transform="translate(-29.284 2034.704)" fill="currentColor"></path>
                                                                    <path id="Path_7" data-name="Path 7" d="M101.834-2026.106l-8.309-1.855a2.866,2.866,0,0,0-2.173.379,2.875,2.875,0,0,0-1.268,1.8l-1.853,8.311a2.879,2.879,0,0,0,2.182,3.439,2.839,2.839,0,0,0,1.161.019,2.871,2.871,0,0,0,2.277-2.2l.427-1.915a22.287,22.287,0,0,1-5.654,21.816,22.282,22.282,0,0,1-26.686,3.624,2.886,2.886,0,0,0-3.928,1.082,2.881,2.881,0,0,0,1.085,3.928,27.8,27.8,0,0,0,13.769,3.63,28.03,28.03,0,0,0,19.834-8.191,28.066,28.066,0,0,0,6.8-28.491l1.085.244a2.882,2.882,0,0,0,3.439-2.184A2.88,2.88,0,0,0,101.834-2026.106Z" transform="translate(-40.353 2046.791)" fill="currentColor"></path>
                                                                </g>
                                                                <g id="Group_5" data-name="Group 5" transform="translate(206.165 107.848)">
                                                                    <g id="Group_3" data-name="Group 3">
                                                                        <path id="Path_8" data-name="Path 8" d="M117.767-2016.034v13.463a2.34,2.34,0,0,1-.725,1.838,2.766,2.766,0,0,1-1.918.647,2.685,2.685,0,0,1-1.864-.647,2.331,2.331,0,0,1-.725-1.838v-13.413a7.348,7.348,0,0,0-1.139-4.58,4.3,4.3,0,0,0-3.574-1.425,5.609,5.609,0,0,0-4.5,1.942,7.905,7.905,0,0,0-1.656,5.306v12.169a2.331,2.331,0,0,1-.725,1.838,2.759,2.759,0,0,1-1.915.647,2.685,2.685,0,0,1-1.864-.647,2.331,2.331,0,0,1-.725-1.838v-13.413a7.348,7.348,0,0,0-1.139-4.58,4.3,4.3,0,0,0-3.574-1.425,5.681,5.681,0,0,0-4.53,1.942,7.813,7.813,0,0,0-1.684,5.306v12.169a2.331,2.331,0,0,1-.725,1.838,2.685,2.685,0,0,1-1.864.647,2.78,2.78,0,0,1-1.889-.647,2.292,2.292,0,0,1-.751-1.838v-21.074a2.282,2.282,0,0,1,.776-1.814,2.755,2.755,0,0,1,1.864-.672,2.536,2.536,0,0,1,1.788.646,2.322,2.322,0,0,1,.7,1.786v1.917a7.92,7.92,0,0,1,3.158-3.315,9.332,9.332,0,0,1,4.662-1.139,8.863,8.863,0,0,1,4.791,1.189,7.131,7.131,0,0,1,2.82,3.678,8.65,8.65,0,0,1,3.366-3.546,9.764,9.764,0,0,1,5.075-1.322Q117.767-2026.235,117.767-2016.034Z" transform="translate(-80.275 2037.109)" fill="currentColor">
                                                                        </path>
                                                                        <path id="Path_9" data-name="Path 9" d="M114.813-2023.7q2.459,2.539,2.46,7.715v13.413a2.423,2.423,0,0,1-.675,1.838,2.564,2.564,0,0,1-1.864.647,2.45,2.45,0,0,1-1.811-.672,2.447,2.447,0,0,1-.675-1.813v-1.915a7.293,7.293,0,0,1-2.924,3.34,8.645,8.645,0,0,1-4.53,1.164,10.267,10.267,0,0,1-4.558-1.01A7.809,7.809,0,0,1,97-2003.814a7.3,7.3,0,0,1-1.164-4.038,6.217,6.217,0,0,1,1.423-4.324,8.5,8.5,0,0,1,4.712-2.252,47.209,47.209,0,0,1,9.034-.672H112.2v-1.5a6.077,6.077,0,0,0-1.192-4.192,4.983,4.983,0,0,0-3.832-1.293,12.239,12.239,0,0,0-3.416.464,25.725,25.725,0,0,0-3.52,1.347,5.09,5.09,0,0,1-1.968.776,1.6,1.6,0,0,1-1.268-.568,2.135,2.135,0,0,1-.492-1.451,2.234,2.234,0,0,1,.441-1.372,4.56,4.56,0,0,1,1.423-1.164,17.741,17.741,0,0,1,4.246-1.581,19.844,19.844,0,0,1,4.712-.6Q112.355-2026.235,114.813-2023.7Zm-4.428,17.891a7.021,7.021,0,0,0,1.814-5v-1.347h-.931a40.65,40.65,0,0,0-6.214.363,6.113,6.113,0,0,0-3.11,1.218,3.168,3.168,0,0,0-.931,2.46,3.883,3.883,0,0,0,1.347,3.053,4.887,4.887,0,0,0,3.366,1.192A6.083,6.083,0,0,0,110.385-2005.808Z" transform="translate(-52.081 2037.109)" fill="currentColor">
                                                                        </path>
                                                                        <path id="Path_10" data-name="Path 10" d="M107.159-2002.155a3.977,3.977,0,0,1-1.322-1.192,2.605,2.605,0,0,1-.388-1.4,2.129,2.129,0,0,1,.467-1.4,1.473,1.473,0,0,1,1.192-.57,5.092,5.092,0,0,1,2.123.83,22.018,22.018,0,0,0,3.028,1.347,11.4,11.4,0,0,0,3.7.517,7.262,7.262,0,0,0,3.883-.88,2.778,2.778,0,0,0,1.4-2.486,2.432,2.432,0,0,0-.543-1.659,4.609,4.609,0,0,0-1.889-1.111,32.111,32.111,0,0,0-3.987-1.063,15.342,15.342,0,0,1-6.551-2.668,5.678,5.678,0,0,1-1.994-4.583,6.678,6.678,0,0,1,1.293-4.012,8.417,8.417,0,0,1,3.574-2.77,12.952,12.952,0,0,1,5.179-.984,14.951,14.951,0,0,1,4.038.546,11.354,11.354,0,0,1,3.47,1.578,3.214,3.214,0,0,1,1.71,2.642,2.06,2.06,0,0,1-.492,1.4,1.5,1.5,0,0,1-1.164.568,2.289,2.289,0,0,1-.933-.206,12.022,12.022,0,0,1-1.243-.675,17.271,17.271,0,0,0-2.615-1.293,8.49,8.49,0,0,0-3.028-.467,5.935,5.935,0,0,0-3.495.931,2.946,2.946,0,0,0-1.322,2.539,2.545,2.545,0,0,0,1.217,2.252,14.86,14.86,0,0,0,4.583,1.527,25.152,25.152,0,0,1,5.435,1.659,6.449,6.449,0,0,1,2.823,2.3,6.536,6.536,0,0,1,.855,3.5,6.374,6.374,0,0,1-2.823,5.412,12.565,12.565,0,0,1-7.533,2.044A15.688,15.688,0,0,1,107.159-2002.155Z" transform="translate(-34.666 2037.109)" fill="currentColor">
                                                                        </path>
                                                                        <path id="Path_11" data-name="Path 11" d="M137.56-2009.028v13.463a2.355,2.355,0,0,1-.725,1.814,2.714,2.714,0,0,1-1.918.672,2.609,2.609,0,0,1-1.887-.672,2.4,2.4,0,0,1-.7-1.814v-13.412a6.822,6.822,0,0,0-1.268-4.555,5,5,0,0,0-3.962-1.45,6.768,6.768,0,0,0-5.075,1.968,7.255,7.255,0,0,0-1.918,5.281v12.169a2.4,2.4,0,0,1-.7,1.814,2.62,2.62,0,0,1-1.889.672,2.714,2.714,0,0,1-1.918-.672,2.355,2.355,0,0,1-.725-1.814v-32.054a2.309,2.309,0,0,1,.754-1.811,2.8,2.8,0,0,1,1.94-.672,2.607,2.607,0,0,1,1.839.647,2.247,2.247,0,0,1,.7,1.734v12.946a8.662,8.662,0,0,1,3.472-3.315,10.54,10.54,0,0,1,4.968-1.139Q137.562-2019.229,137.56-2009.028Z" transform="translate(-17.584 2030.102)" fill="currentColor">
                                                                        </path>
                                                                        <path id="Path_12" data-name="Path 12" d="M147.648-1995.512a2.438,2.438,0,0,1-.672,1.709,2.126,2.126,0,0,1-1.606.723,2.52,2.52,0,0,1-1.76-.723L130.716-2005.2v9.579a2.4,2.4,0,0,1-.725,1.89,2.683,2.683,0,0,1-1.861.647,2.783,2.783,0,0,1-1.892-.647,2.358,2.358,0,0,1-.751-1.89v-31.949a2.358,2.358,0,0,1,.751-1.89,2.8,2.8,0,0,1,1.892-.647,2.7,2.7,0,0,1,1.861.647,2.4,2.4,0,0,1,.725,1.89v20.3l11.806-11.027a2.624,2.624,0,0,1,1.71-.779,2.335,2.335,0,0,1,1.707.725,2.34,2.34,0,0,1,.725,1.71,2.453,2.453,0,0,1-.88,1.811l-9.217,8.441,10.15,8.958A2.408,2.408,0,0,1,147.648-1995.512Z" transform="translate(1.635 2030.102)" fill="currentColor">
                                                                        </path>
                                                                        <path id="Path_13" data-name="Path 13" d="M140.223-2001.588a10.828,10.828,0,0,1-4.353-4.583,14.936,14.936,0,0,1-1.527-6.912,15.065,15.065,0,0,1,1.527-6.965,10.847,10.847,0,0,1,4.353-4.583,12.987,12.987,0,0,1,6.549-1.606,13.013,13.013,0,0,1,6.551,1.606,10.84,10.84,0,0,1,4.35,4.583,15.065,15.065,0,0,1,1.527,6.965,14.936,14.936,0,0,1-1.527,6.912,10.821,10.821,0,0,1-4.35,4.583,13.015,13.015,0,0,1-6.551,1.606A12.99,12.99,0,0,1,140.223-2001.588Zm11.857-4.816q1.835-2.279,1.839-6.678,0-4.352-1.864-6.655a6.412,6.412,0,0,0-5.283-2.3,6.406,6.406,0,0,0-5.28,2.3q-1.864,2.307-1.864,6.655,0,4.4,1.839,6.678a6.409,6.409,0,0,0,5.306,2.28A6.418,6.418,0,0,0,152.08-2006.4Z" transform="translate(17.684 2037.109)" fill="currentColor">
                                                                        </path>
                                                                        <path id="Path_14" data-name="Path 14" d="M160.989-2024.069a2.324,2.324,0,0,1-.568,1.71,3.077,3.077,0,0,1-1.968.672l-1.555.157a6.7,6.7,0,0,0-4.842,2.275,7.371,7.371,0,0,0-1.58,4.766v11.857a2.325,2.325,0,0,1-.723,1.864,2.754,2.754,0,0,1-1.864.621,2.843,2.843,0,0,1-1.889-.621,2.271,2.271,0,0,1-.754-1.864v-21.074a2.3,2.3,0,0,1,.754-1.839,2.786,2.786,0,0,1,1.889-.646,2.466,2.466,0,0,1,1.735.646,2.323,2.323,0,0,1,.7,1.786v2.485a7.6,7.6,0,0,1,3.006-3.467,10.068,10.068,0,0,1,4.451-1.4l.725-.05Q160.987-2026.348,160.989-2024.069Z" transform="translate(37.435 2037.169)" fill="currentColor">
                                                                        </path>
                                                                    </g>
                                                                    <g id="Group_4" data-name="Group 4" transform="translate(192.795 31.393)">
                                                                        <path id="Path_15" data-name="Path 15" d="M153.092-2018.569a2.731,2.731,0,0,1,1.012,1.012,2.835,2.835,0,0,1,.368,1.431,2.832,2.832,0,0,1-.368,1.428,2.757,2.757,0,0,1-1.018,1.015,2.783,2.783,0,0,1-1.426.374,2.8,2.8,0,0,1-1.431-.374,2.729,2.729,0,0,1-1.015-1.015,2.8,2.8,0,0,1-.371-1.428,2.808,2.808,0,0,1,.371-1.431,2.7,2.7,0,0,1,1.015-1.012,2.848,2.848,0,0,1,1.431-.368A2.833,2.833,0,0,1,153.092-2018.569Zm-.225,4.516a2.193,2.193,0,0,0,.838-.857,2.46,2.46,0,0,0,.3-1.215,2.45,2.45,0,0,0-.3-1.215,2.22,2.22,0,0,0-.838-.855,2.377,2.377,0,0,0-1.206-.309,2.392,2.392,0,0,0-1.206.309,2.214,2.214,0,0,0-.844.855,2.45,2.45,0,0,0-.3,1.215,2.459,2.459,0,0,0,.3,1.215,2.188,2.188,0,0,0,.844.857,2.367,2.367,0,0,0,1.206.313A2.352,2.352,0,0,0,152.867-2014.053Zm.219-.97a.38.38,0,0,1,.1.242.3.3,0,0,1-.135.253.488.488,0,0,1-.306.1.424.424,0,0,1-.377-.2l-.624-.905a.473.473,0,0,0-.129-.138.309.309,0,0,0-.169-.042h-.211v.844a.423.423,0,0,1-.121.326.42.42,0,0,1-.307.113.443.443,0,0,1-.318-.113.424.424,0,0,1-.124-.326v-2.514a.432.432,0,0,1,.1-.3.393.393,0,0,1,.3-.1h1.119a1.343,1.343,0,0,1,.9.269.944.944,0,0,1,.315.754,1,1,0,0,1-.205.638.992.992,0,0,1-.574.346.7.7,0,0,1,.256.112,1.144,1.144,0,0,1,.233.242Zm-1.322-1.324a.633.633,0,0,0,.382-.09.355.355,0,0,0,.112-.295.362.362,0,0,0-.112-.307.642.642,0,0,0-.382-.087h-.543v.779Z" transform="translate(-148.843 2018.937)" fill="currentColor">
                                                                        </path>
                                                                    </g>
                                                                </g>
                                                            </g>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Shipping Type') }}:</span>
                                                        <span class="fw-600 ml-2">Mashkor</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Shipping Cost') }}:</span>
                                                        <span class="fw-600 ml-2">{{env('MASHKOR_PRICE')}}</span>
                                                    </div>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @endif
                                @if (get_setting('armada_status') == 1)
                                    <div class="col-md-6 mb-3">
                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" name="shipping_company" value="armada" required>
                                            <input type="hidden" name="shipping_cost" value="{{env('ARMADA_PRICE')}}"/>
                                            <span class="d-flex p-3 aiz-megabox-elem">
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                
                                                <span class="flex-grow-1 pl-3 text-left">
                                                    <div>
                                                        <img src="{{ static_asset('assets/img/shipment/armada.png') }}" height="30">
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Shipping Type') }}:</span>
                                                        <span class="fw-600 ml-2">Armada</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Shipping Cost') }}:</span>
                                                        <span class="fw-600 ml-2">{{env('ARMADA_PRICE')}}</span>
                                                    </div>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @endif
                                @if(get_setting('quick_status') == 1)
                                    <div class="col-md-6 mb-3">
                                        <label class="aiz-megabox d-block bg-white mb-0">
                                            <input type="radio" name="shipping_company" value="quick" required>
                                            <input type="hidden" name="shipping_cost" value="{{env('QUICK_PRICE')}}">
                                            <span class="d-flex p-3 aiz-megabox-elem">
                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                <span class="flex-grow-1 pl-3 text-left">
                                                    <div>
                                                        <img src="{{ static_asset('assets/img/shipment/quick.png') }}" height="30">
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Shipping Type') }}:</span>
                                                        <span class="fw-600 ml-2">Quick</span>
                                                    </div>
                                                    <div>
                                                        <span class="opacity-60">{{ translate('Shipping Cost') }}:</span>
                                                        <span class="fw-600 ml-2">{{env('QUICK_PRICE')}}</span>
                                                    </div>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @endif
                            </div>--}}
                        </div>
                        <div id="pick_up_point_section" class="d-none">
                            @include('frontend.partials.pick_up_points')
                        </div>
                    @endif
                    <div class="row align-items-center p-4">
                        <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                            <a href="{{ route('home') }}" class="btn btn-link">
                                <i class="las la-arrow-left"></i>
                                {{ translate('Return to shop')}}
                            </a>
                        </div>
                        <div class="col-md-6 text-center text-md-right">
                            <button type="submit" class="btn btn-primary fw-600">{{ translate('Continue to Delivery Info')}}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script>

    function show_pickup_point(el, type) {
        var value = $(el).val();
        var target = $(el).data('target');

        // Hide all sections first
        $('#delivery_info').addClass('d-none');
        $('#pick_up_point_section').addClass('d-none');
        $('.carrier_id_' + type).addClass('d-none');

        // Reset selections
        $('input[name="address_id"]').prop('checked', false);
        $('input[name="pickup_point_id"]').prop('checked', false);

        if (value == 'home_delivery' || value == 'carrier') {
            $('#delivery_info').removeClass('d-none');
            $('.carrier_id_' + type).removeClass('d-none');
        } else if (value == 'pickup_point') {
            $('#pick_up_point_section').removeClass('d-none');
        }
    }


// Add this to ensure the correct section is shown on page load
$(document).ready(function() {
    $('#checkout-form').submit(function(e) {
        e.preventDefault(); // Prevent the form from submitting initially

        // get value of input name shipping_type
        const shippingType = $('input[name="shipping_type"]:checked').val();
        // Check if the shipping type is pickup_point
        if (shippingType === 'pickup_point') {
            // If pickup point is not selected, display an error message
            const pickupPointId = $('input[name="pickup_point_id"]:checked').val();
            if (!pickupPointId) {
                alert('Please select a pickup point');
                return;
            }
        }

        // If all validations pass, submit the form
        this.submit();
    });
    $('input[name^="shipping_type_"]:checked').each(function() {
        show_pickup_point(this, $(this).attr('name').split('_').pop());
    });
});

</script>
@endsection

@section('modal')
    @include('frontend.partials.address_modal')
@endsection