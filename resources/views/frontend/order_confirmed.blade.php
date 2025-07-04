@extends('frontend.layouts.app')

@section('content')
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
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('1. My Cart')}}</h3>
                            </div>
                        </div>
                        <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-map"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Shipping info')}}</h3>
                            </div>
                        </div>
                        @if($delivery_info_step_active == true)
                            <div class="col done">
                                <div class="text-center text-success">
                                    <i class="la-3x mb-2 las la-truck"></i>
                                    <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('3. Delivery info')}}</h3>
                                </div>
                            </div>
                        @endif
                        <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-credit-card"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ $delivery_info_step_active == true ? translate('4. Payment') : translate('3. Payment') }}</h3>
                            </div>
                        </div>
                        <div class="col active">
                            <div class="text-center text-primary">
                                <i class="la-3x mb-2 las la-check-circle"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ $delivery_info_step_active == true ? translate('5. Confirmation') : translate('4. Confirmation') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-4">
        <div class="container text-left">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    @php
                        $first_order = \App\Models\Order::where('combined_order_id',$combined_order->id)->first();
                        $user = \App\Models\User::where('id', $combined_order->user_id)->first();
                    @endphp
                    <div class="text-center py-4 mb-4">
                        <i class="la la-check-circle la-3x text-success mb-3"></i>
                        <h1 class="h3 mb-3 fw-600">{{ translate('Thank You for Your Order!')}}</h1>
                        <p class="opacity-70 font-italic">{{  translate('A copy or your order summary has been sent to') }} {{ $user->email }}</p>
                    </div>
                    <div class="mb-4 bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-600 mb-3 fs-17 pb-2">{{ translate('Order Summary')}}</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Order date')}}:</td>
                                        <td>{{ date('d-m-Y H:i A', $first_order->date) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Name')}}:</td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Email')}}:</td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ $first_order->shipping_address != 'null' ? translate('Shipping address') : translate('Pickup Point')}}:</td>
                                        @if($first_order->shipping_address != 'null')
                                            <td><address>
                                                    <strong class="text-main">
                                                        {{ translate('Name') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->name ?? "" }} <br>
                                                    <strong class="text-main">
                                                        {{ translate('Email') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->email ?? "" }}<br>
                                                    <strong class="text-main">
                                                        {{ translate('Phone') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->phone ?? "" }}, <strong class="text-main">
                                                        {{ translate('Type') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->address_type ?? '' }} <br>
                                                    <strong class="text-main">
                                                        {{ translate('State') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->state ?? "" }}, <strong class="text-main">
                                                        {{ translate('City') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->city ?? "" }}, <strong class="text-main">
                                                        {{ translate('Block') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->bloc ?? "" }}, <strong class="text-main">
                                                        {{ translate('Avenue') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->avenue ?? "" }}, <strong class="text-main">
                                                        {{ translate('Street') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->street ?? "" }} <br>
                                                    <strong class="text-main">
                                                        {{ translate('House') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->house ?? "" }}, <strong class="text-main">
                                                        {{ translate('Address') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->address ?? "" }}, <strong class="text-main">
                                                        {{ translate('Address Label') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->address_label ?? "" }}, <strong class="text-main">
                                                        {{ translate('Building Name') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->building_name ?? "" }}, <strong class="text-main">
                                                        {{ translate('Building Number') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->building_number ?? "" }} <br>
                                                    <strong class="text-main">
                                                        {{ translate('Floor') }}:
                                                    </strong> {{ json_decode($first_order->shipping_address)->floor ?? '' }}
                                                </address></td>
                                        @else
                                            @php
                                                $pickup_point = \App\Models\PickupPoint::find($first_order->pickup_point_id);
                                            @endphp
                                            <td>
                                                <address>
                                                    <strong class="text-main">{{ translate('Pickup Point name') }}:</strong> {{ $pickup_point->getTranslation('name') }} <br>
                                                    <strong class="text-main">{{ translate('Address') }}:</strong> {{ $pickup_point->getTranslation('address') }} <br>
                                                    <strong class="text-main">{{ translate('Phone') }}:</strong> {{ $pickup_point->phone }}
                                                </address>
                                            </td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Shipping company')}}:</td>
                                        <td>{{ $first_order->shipping_company }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Order status')}}:</td>
                                        <td>{{ translate(ucfirst(str_replace('_', ' ', $first_order->delivery_status))) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Total order amount')}}:</td>
                                        <td>{{ single_price($combined_order->grand_total) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Shipping')}}:</td>
                                        <td>{{ translate('Flat shipping rate')}}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Payment method')}}:</td>
                                        <td>{{ translate(ucfirst(str_replace('_', ' ', $first_order->payment_type))) }}</td>
                                    </tr>
                                    <!-- Add Wallet Balance Row -->
                                    @if($first_order->payment_type='wallet' || $first_order->payment_type='wallet_upay')
                                    <tr>
                                        <td class="w-50 fw-600">{{ translate('Wallet Balance Used')}}:</td>
                                        <td>{{ $first_order->wallet_balance ? single_price($first_order->wallet_balance) : translate('N/A') }}</td>
                                    </tr>
                                @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    @foreach ($combined_order->orders as $order)
                        <div class="card shadow-sm border-0 rounded">
                            <div class="card-body">
                                <div class="text-center py-4 mb-4">
                                    <h2 class="h5">{{ translate('Order Code:')}} <span class="fw-700 text-primary">{{ $order->code }}</span></h2>
                                </div>
                                <div>
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th width="30%">{{ translate('Product')}}</th>
                                                <th>{{ translate('Variation')}}</th>
                                                <th>{{ translate('AddOns') }}</th>
                                                <th>{{ translate('Quantity')}}</th>
                                                <th>{{ translate('Delivery Type')}}</th>
                                                <th class="text-right">{{ translate('Price')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->orderDetails as $key => $orderDetail)
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>
                                                        @if ($orderDetail->product != null)
                                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-reset">
                                                                {{ $orderDetail->product->getTranslation('name') }}
                                                                @php
                                                                    if($orderDetail->combo_id != null) {
                                                                        $combo = \App\ComboProduct::findOrFail($orderDetail->combo_id);

                                                                        echo '('.$combo->combo_title.')';
                                                                    }
                                                                @endphp
                                                            </a>
                                                        @else
                                                            <strong>{{  translate('Product Unavailable') }}</strong>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $orderDetail->variation }}
                                                    </td>
                                                    <td class="row row-cols-1">
                                                        @if($orderDetail->addons != null)
                                                            @foreach (json_decode($orderDetail->addons) as $key => $addon)
                                                                <div>
                                                                    @php
                                                                        $addonDetail = \App\Models\CategoryAddon::find($addon->id);
                                                                    @endphp
                                                                    @if($addon->quantity > 0)
                                                                        <strong class="addon-name">{{ $addonDetail->name }}: </strong>
                                                                        {{ single_price($addonDetail->price) }} x {{ $addon->quantity }}
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $orderDetail->quantity }}
                                                    </td>
                                                    <td>
                                                        @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                                            {{  translate('Home Delivery') }}
                                                        @elseif ($order->shipping_type != null && $order->shipping_type == 'carrier')
                                                            {{  translate('Carrier') }}
                                                        @elseif ($order->shipping_type == 'pickup_point')
                                                            @if ($order->pickup_point != null)
                                                                {{ $order->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td class="text-right">{{ single_price($orderDetail->price) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
