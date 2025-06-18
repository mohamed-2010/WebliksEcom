@extends('backend.layouts.app')

@section('content')
<style>

    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 1px solid #aaa;
        border-radius: 4px;
        padding: 0.6rem 1rem;
        font-size: 0.875rem;
        height: calc(1.3125rem + 1.2rem + 2px);
        line-height: 1.5;
    }
    .select2-container .select2-selection--single {
        height: 44px!important;
    }
</style>

    <div class="card">
        <div class="card-header">
            <h1 class="h2 fs-16 mb-0">{{ translate('Order Details') }}</h1>
        </div>
        <div class="card-body">
            <div class="row gutters-5">
                {{-- <div class="col text-md-left text-center">
                </div> --}}
                @php
                    $delivery_status = $order->delivery_status;
                    $payment_status = $order->payment_status;
                    $admin_user_id = App\Models\User::where('user_type', 'admin')->first()->id;
                @endphp

                <!--Assign Delivery Boy-->
                @if ($order->seller_id == $admin_user_id || get_setting('product_manage_by_admin') == 1)

                <div class="col-md-3 ml-auto">
                    <label for="update_shipping_company">{{ translate('Shipping Company') }}</label>
                    @if (auth()->user()->can('update_order_shipping_company'))
                        <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                            id="update_shipping_company">
                            <option value="">{{ translate('Select Shipping Company') }}</option>
                            @if (get_setting('mashkor_status') == 1)
                                <option value="mashkor" @if ($order->shipping_company == 'mashkor') selected @endif>
                                    {{ translate('Mashkor') }}
                                </option>
                            @endif
                            @if (get_setting('armada_status') == 1)
                                <option value="armada" @if ($order->shipping_company == 'armada') selected @endif>
                                    {{ translate('Armada') }}
                                </option>
                            @endif
                            @if(get_setting('quick_status') == 1)
                                <option value="quick" @if ($order->shipping_company == 'quick') selected @endif>
                                    {{ translate('Quick') }}
                                </option>
                            @endif
                        </select>
                    @else
                        <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                    @endif
                </div>


                    @if (addon_is_activated('delivery_boy') && $delivery_boys)
                        <div class="col-md-3 ml-auto">
                            <label for="assign_deliver_boy">{{ translate('Assign Deliver Boy') }}</label>
                            @if (($delivery_status == 'pending' || $delivery_status == 'confirmed' || $delivery_status == 'picked_up') && auth()->user()->can('assign_delivery_boy_for_orders'))
                                <select class="form-control aiz-selectpicker" data-live-search="true"
                                    data-minimum-results-for-search="Infinity" id="assign_deliver_boy">
                                    <option value="">{{ translate('Select Delivery Boy') }}</option>
                                    @foreach ($delivery_boys as $delivery_boy)
                                        <option value="{{ $delivery_boy->id }}"
                                            @if ($order->assign_delivery_boy == $delivery_boy->id) selected @endif>
                                            {{ $delivery_boy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="{{ optional($order->delivery_boy)->name }}"
                                    disabled>
                            @endif
                        </div>
                    @endif

                    <div class="col-md-3 ml-auto">
                        <label for="update_payment_status">{{ translate('Payment Status') }}</label>
                        @if (auth()->user()->can('update_order_payment_status'))
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                                id="update_payment_status">
                                <option value="unpaid" @if ($payment_status == 'unpaid') selected @endif>
                                    {{ translate('Unpaid') }}
                                </option>
                                <option value="paid" @if ($payment_status == 'paid') selected @endif>
                                    {{ translate('Paid') }}
                                </option>
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $payment_status }}" disabled>
                        @endif
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for="update_delivery_status">{{ translate('Delivery Status') }}</label>
                        @if (auth()->user()->can('update_order_delivery_status') && $delivery_status != 'delivered' && $delivery_status != 'cancelled')
                            <select class="form-control aiz-selectpicker" data-minimum-results-for-search="Infinity"
                                id="update_delivery_status">
                                <option value="pending" @if ($delivery_status == 'pending') selected @endif>
                                    {{ translate('Pending') }}
                                </option>
                                <option value="confirmed" @if ($delivery_status == 'confirmed') selected @endif>
                                    {{ translate('Confirmed') }}
                                </option>
                                <option value="picked_up" @if ($delivery_status == 'picked_up') selected @endif>
                                    {{ translate('Picked Up') }}
                                </option>
                                <option value="on_the_way" @if ($delivery_status == 'on_the_way') selected @endif>
                                    {{ translate('On The Way') }}
                                </option>
                                <option value="delivered" @if ($delivery_status == 'delivered') selected @endif>
                                    {{ translate('Delivered') }}
                                </option>
                                <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>
                                    {{ translate('Cancel') }}
                                </option>
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $delivery_status }}" disabled>
                        @endif
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for="update_tracking_code">
                            {{ translate('Tracking Code (optional)') }}
                        </label>
                        <input type="text" class="form-control" id="update_tracking_code"
                            value="{{ $order->tracking_code }}">
                    </div>
                @endif
            </div>
            <div class="mb-3">
                @php
                    $removedXML = '<?xml version="1.0" encoding="UTF-8"?>';
                @endphp
                {!! str_replace($removedXML, '', QrCode::size(100)->generate($order->code)) !!}
            </div>
            <div class="row gutters-5">
                <div class="col text-md-left text-center">
                    @if($order->shipping_type == 'home_delivery' || $order->shipping_type == 'pos')
                        @if(json_decode($order->shipping_address))
                            <address>
                                <strong class="text-main">
                                    {{ translate('Name') }}:
                                @php $shipping_address = json_decode($order->shipping_address); @endphp
                                </strong> {{ $order->user->name ?? $order->guest->name ?? $shipping_address->name ?? "" }} <br>
                                <strong class="text-main">
                                    {{ translate('Email') }}:
                                </strong> {{ $order->user->email ?? $order->guest->email ?? $shipping_address->email ?? "" }} <br>
                                <strong class="text-main">
                                    {{ translate('Phone') }}:
                                </strong> {{ $order->user->phone ?? $order->guest->phone ?? $shipping_address->phone ?? "" }}, <strong class="text-main">
                                    {{ translate('Type') }}:
                                </strong> {{ json_decode($order->shipping_address)->address_type ?? '' }} <br>
                                <strong class="text-main">
                                    {{ translate('State') }}:
                                </strong> {{ json_decode($order->shipping_address)->state ?? "" }}, <strong class="text-main">
                                    {{ translate('City') }}:
                                </strong> {{ json_decode($order->shipping_address)->city ?? "" }}, <strong class="text-main">
                                    {{ translate('Block') }}:
                                </strong> {{ json_decode($order->shipping_address)->bloc ?? "" }}, <strong class="text-main">
                                    {{ translate('Avenue') }}:
                                </strong> {{ json_decode($order->shipping_address)->avenue ?? "" }}, <strong class="text-main">
                                    {{ translate('Street') }}:
                                </strong> {{ json_decode($order->shipping_address)->street ?? "" }} <br>
                                <strong class="text-main">
                                    {{ translate('House') }}:
                                </strong> {{ json_decode($order->shipping_address)->house ?? "" }}, <strong class="text-main">
                                    {{ translate('Address') }}:
                                </strong> {{ json_decode($order->shipping_address)->address ?? "" }}, <strong class="text-main">
                                    {{ translate('Address Label') }}:
                                </strong> {{ json_decode($order->shipping_address)->address_label ?? "" }}, <strong class="text-main">
                                    {{ translate('Building Name') }}:
                                </strong> {{ json_decode($order->shipping_address)->building_name ?? "" }},
                                @isset(json_decode($order->shipping_address)->address_type)
                                    @if(json_decode($order->shipping_address)->address_type == "apartment" || json_decode($order->shipping_address)->address_type == "office")
                                    <strong class="text-main">
                                        {{ translate('Building Number') }}:
                                    </strong>
                                    @else
                                    <strong class="text-main">
                                        {{ translate('House Number') }}:
                                    </strong>
                                    @endif
                                @endisset
                                {{ json_decode($order->shipping_address)->building_number ?? "" }} <br>
                                @isset(json_decode($order->shipping_address)->address_type)
                                    @if(json_decode($order->shipping_address)->address_type != "house")
                                    <strong class="text-main">
                                        {{ translate('Floor') }}:
                                    </strong> {{ json_decode($order->shipping_address)->floor ?? '' }}
                                    <strong class="text-main">
                                        {{ translate('Apt.Number') }}:
                                    </strong> {{ json_decode($order->shipping_address)->apt_number ?? '' }}
                                    @endif
                                @endisset
                                {{-- {{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}, @if(isset(json_decode($order->shipping_address)->state)) {{ json_decode($order->shipping_address)->state }}@endif<br> --}}
                                {{--{{ json_decode($order->shipping_address)->country }}--}}
                            </address>
                        @else
                            @if($order->user)
                                <address>
                                    <strong class="text-main">
                                        {{ translate('Name') }}:
                                    </strong> {{ $order->user->name }} <br>
                                    <strong class="text-main">
                                        {{ translate('Email') }}:
                                    </strong> {{ $order->user->email }}<br>
                                    <strong class="text-main">
                                        {{ translate('Phone') }}:
                                    </strong> {{ $order->user->phone }}
                                </address>
                            @endif
                        @endif
                    @elseif($order->shipping_type == 'pickup_point')
                        <address>
                            <strong class="text-main">
                                {{ translate('Pickup Point') }}:
                            </strong> {{ $order->pickup_point->getTranslation('name') }} <br>
                            <strong class="text-main">
                                {{ translate('Address') }}:
                            </strong> {{ $order->pickup_point->getTranslation('address') }}
                        </address>
                    @endif
                    @if ($order->manual_payment && is_array(json_decode($order->manual_payment_data, true)))
                        <br>
                        <strong class="text-main">{{ translate('Payment Information') }}</strong><br>
                        {{ translate('Name') }}: {{ json_decode($order->manual_payment_data)->name }},
                        {{ translate('Amount') }}:
                        {{ single_price(json_decode($order->manual_payment_data)->amount) }},
                        {{ translate('TRX ID') }}: {{ json_decode($order->manual_payment_data)->trx_id }}
                        <br>
                        <a href="{{ isset(json_decode($order->manual_payment_data)->photo) ? uploaded_asset(json_decode($order->manual_payment_data)->photo) : '' }}" target="_blank">
                            <img src="{{ isset(json_decode($order->manual_payment_data)->photo) ? uploaded_asset(json_decode($order->manual_payment_data)->photo) : '' }}" alt=""
                                height="100">
                        </a>
                    @endif
                </div>
                <div class="col-md-4 ml-auto">
                    <table>
                        <tbody>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order #') }}</td>
                                <td class="text-info text-bold text-right"> {{ $order->code }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order Status') }}</td>
                                <td class="text-right">
                                    @if ($delivery_status == 'delivered')
                                        <span class="badge badge-inline badge-success">
                                            {{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}
                                        </span>
                                        @elseif ($delivery_status == 'pending')
                                        <span class="badge badge-inline badge-warning">
                                            {{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}
                                        </span>
                                    @else
                                        <span class="badge badge-inline badge-info">
                                            {{ translate(ucfirst(str_replace('_', ' ', $delivery_status))) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Order Date') }} </td>
                                <td class="text-right">{{ date('d-m-Y h:i A', $order->date) }}</td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">
                                    {{ translate('Total amount') }}
                                </td>
                                <td class="text-right">
                                    @php
                                        $order_total_addons = 0;
                                    @endphp
                                    @foreach ($order->orderDetails as $key => $orderDetail)
                                        @php
                                            $total_addons = 0;
                                            if($orderDetail->addons != null){
                                                foreach (json_decode($orderDetail->addons) as $addon) {
                                                    $total_addons += $addon->price*$addon->quantity;
                                                }
                                            }
                                            $order_total_addons += $total_addons;
                                        @endphp
                                    @endforeach
                                    {{  single_price($order->orderDetails->sum('price') + $order_total_addons + $order->orderDetails->sum('shipping_cost') + $order->orderDetails->sum('tax') - $order->coupon_discount) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-main text-bold">{{ translate('Payment method') }}</td>
                                <td class="text-right">
                                    @if($order->payment_type == 'Wallet_upay')
                                        {{ translate('Wallet & upay') }}
                                    @else
                                        {{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="text-main text-bold">{{ translate('Additional Info') }}</td>
                                <td class="text-right">{{ $order->additional_info }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="new-section-sm bord-no">
            <div class="row">
                <div class="col-lg-12 table-responsive">
                    <table class="table-bordered aiz-table invoice-summary table">
                        <thead>
                            <tr class="bg-trans-dark">
                                <th data-breakpoints="lg" class="min-col">#</th>
                                <th width="10%">{{ translate('Photo') }}</th>
                                <th class="text-uppercase">{{ translate('Description') }}</th>

                                @php
                                    $addons = [];
                                    foreach ($order->orderDetails as $key => $orderDetail) {
                                        if($orderDetail->addons != null){
                                            foreach (json_decode($orderDetail->addons) as $addon) {
                                                $addons[] = $addon;
                                            }
                                        }
                                    }
                                @endphp

                                @if(count($addons) > 0)
                                    <th class="text-uppercase">{{ translate('AddOns') }}</th>
                                @endif

                                {{--@foreach ($order->orderDetails as $key => $orderDetail)


                                        @if($orderDetail->addons == "[]")
                                            @break
                                        @else
                                            <th class="text-uppercase">{{ translate('AddOns') }} {{$orderDetail->addons}}</th>
                                        @endif

                                @endforeach--}}

                                <th data-breakpoints="lg" class="text-uppercase">{{ translate('Delivery Type') }}</th>
                                <th data-breakpoints="lg" class="min-col text-uppercase text-center">
                                    {{ translate('Qty') }}
                                </th>
                                <th data-breakpoints="lg" class="min-col text-uppercase text-center">
                                    {{ translate('Price') }}</th>
                                @if(count($addons) > 0)
                                    <th data-breakpoints="lg" class="min-col text-uppercase text-center">
                                        {{ translate('AddOn Price') }}</th>
                                @endif
                                <th data-breakpoints="lg" class="min-col text-uppercase text-right">
                                    {{ translate('Total') }}</th>
                                <th data-breakpoints="lg" class="min-col text-right text-uppercase">{{translate('DELETE')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderDetails as $key => $orderDetail)
                                    @php
                                        $item_total_addons = 0;
                                        if($orderDetail->addons != null){
                                            foreach (json_decode($orderDetail->addons) as $addon) {
                                                $item_total_addons += $addon->price*$addon->quantity;
                                            }
                                        }
                                    @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank">
                                                <img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}">
                                            </a>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank">
                                                <img height="50" src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}">
                                            </a>
                                        @else
                                            <strong>{{ translate('N/A') }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($orderDetail->product != null && $orderDetail->product->auction_product == 0)
                                            <strong>
                                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank"
                                                    class="text-muted">
                                                    {{ $orderDetail->product->getTranslation('name') }}
                                                </a>
                                            </strong>
                                            <small>
                                                {{ $orderDetail->variation }}
                                            </small>
                                            <br>
                                            <small>
                                                @php
                                                    $product_stock = json_decode($orderDetail->product->stocks->first(), true);
                                                @endphp
                                                {{translate('SKU')}}: {{ $product_stock['sku'] }}
                                            </small>
                                        @elseif ($orderDetail->product != null && $orderDetail->product->auction_product == 1)
                                            <strong>
                                                <a href="{{ route('auction-product', $orderDetail->product->slug) }}" target="_blank"
                                                    class="text-muted">
                                                    {{ $orderDetail->product->getTranslation('name') }}
                                                </a>
                                            </strong>
                                        @else
                                            <strong>{{ translate('Product Unavailable') }}</strong>
                                        @endif
                                    </td>
                                    @if(count($addons) > 0)
                                        <td class="row row-cols-1">
                                            @if($orderDetail->addons != null)
                                                @foreach (json_decode($orderDetail->addons) as $key => $addon)
                                                    <div>
                                                        @php
                                                            $addonDetail = \App\Models\CategoryAddon::find($addon->id);
                                                        @endphp
                                                        <strong class="addon-name">{{ $addonDetail->name }}: </strong>
                                                        {{ single_price($addonDetail->price) }} x {{ $addon->quantity }}
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                    @endif
                                    {{--@if($orderDetail->addons != "[]")
                                    <td class="row row-cols-1">
                                        @if($orderDetail->addons != null)
                                            @foreach (json_decode($orderDetail->addons) as $key => $addon)
                                                <div>
                                                    @php
                                                        $addonDetail = \App\Models\CategoryAddon::find($addon->id);
                                                    @endphp
                                                    <strong class="addon-name">{{ $addonDetail->name }}: </strong>
                                                    {{ single_price($addonDetail->price) }} x {{ $addon->quantity }}
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                   @endif--}}
                                    <td>
                                        @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                            {{ translate('Home Delivery') }}
                                        @elseif ($order->shipping_type == 'pickup_point')
                                            @if ($order->pickup_point != null)
                                                {{ $order->pickup_point->getTranslation('name') }}
                                                ({{ translate('Pickup Point') }})
                                            @else
                                                {{ translate('Pickup Point') }}
                                            @endif
                                        @elseif($order->shipping_type == 'carrier')
                                            @if ($order->carrier != null)
                                                {{ $order->carrier->name }} ({{ translate('Carrier') }})
                                                <br>
                                                {{ translate('Transit Time').' - '.$order->carrier->transit_time }}
                                            @else
                                                {{ translate('Carrier') }}
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $orderDetail->quantity }}
                                    </td>
                                    <td class="text-center">
                                        {{ single_price($orderDetail->price / $orderDetail->quantity) }}
                                    </td>
                                    @if(count($addons) > 0)
                                        <td class="text-center">
                                            {{ single_price($item_total_addons) }}
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        {{ single_price($orderDetail->price + $item_total_addons) }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{route('item.destroy', ['id'=>$orderDetail->id,'order'=>$order->id])}}" class="btn btn-soft-danger btn-icon btn-circle btn-sm " >
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- @if(Auth::user()->user_type == 'admin' || Auth::user()->staff->role->name == 'Manager') --}}
                    @can('add_order_item')
                        <div class="row lg-gutters-12">

                        <div class="col-md-5 ">
                            <label for="update_payment_status">{{ translate('Select Product') }} : </label>
                            <div class="col-md-12 ">
                                <select class="livesearch form-control " name="livesearch"></select>
                            </div>

                        </div>
                            <div class="col-md-5 ">
                                <label for="update_payment_status">{{ translate('Select Variant') }} : </label>
                                <div class="col-md-12  ">
                                    <select class="form-control  productsize">

                                    </select>
                                </div>

                            </div>
                            <div class="col-md-2">

                                <br>

                                <button  id="add-item"  class="btn btn-soft-success"  type="submit">
                                    {{ translate('Add Item') }}
                                </button>

                            </div>
                        </div>

                    {{-- @endif --}}
                    @endcan

                </div>
            </div>
            <div class="clearfix float-right">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Sub Total') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('price') + $order_total_addons) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Tax') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('tax')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Shipping') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->orderDetails->sum('shipping_cost')) }}
                            </td>
                        </tr>
                        @if ($order->payment_type == 'Wallet_upay' || $order->payment_type == 'wallet')
                            <tr>
                                <td>
                                    <strong class="text-muted">{{ translate('Wallet Balance') }} :</strong>
                                </td>
                                <td>
                                    {{ single_price($order->wallet_balance) }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('Coupon') }} :</strong>
                            </td>
                            <td>
                                {{ single_price($order->coupon_discount) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong class="text-muted">{{ translate('TOTAL') }} :</strong>
                            </td>
                            <td class="text-muted h5">
                                @php
                                    $subtotal = $order->orderDetails->sum('price') + $order_total_addons;
                                    $tax = $order->orderDetails->sum('tax');
                                    $shipping = $order->orderDetails->sum('shipping_cost');
                                    $coupon_discount = $order->coupon_discount;
                                    $wallet_balance = ($order->payment_type == 'Wallet_upay' || $order->payment_type == 'wallet') ? $order->wallet_balance : 0;
                                    $total = $subtotal + $tax + $shipping - $coupon_discount - $wallet_balance;
                                @endphp
                                {{ single_price($total) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="no-print text-right">
                    <a href="{{ route('invoice.download', $order->id) }}" type="button" class="btn btn-icon btn-light"><i
                            class="las la-print"></i></a>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        $('#assign_deliver_boy').on('change', function() {
            var order_id = {{ $order->id }};
            var delivery_boy = $('#assign_deliver_boy').val();
            $.post('{{ route('orders.delivery-boy-assign') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                delivery_boy: delivery_boy
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Delivery boy has been assigned') }}');
            });
        });
        $('#update_delivery_status').on('change', function() {
            var order_id = {{ $order->id }};
            var status = $('#update_delivery_status').val();
            $.post('{{ route('orders.update_delivery_status') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                status: status
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Delivery status has been updated') }}');
            });
        });
        $('#update_shipping_company').on('change', function() {
            var order_id = {{ $order->id }};
            var shipping_company = $('#update_shipping_company').val();
            $.post('{{ route('orders.update_shipping_company') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                shipping_company: shipping_company
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Shipping company has been updated') }}');
            });
        });
        $('#update_payment_status').on('change', function() {
            var order_id = {{ $order->id }};
            var status = $('#update_payment_status').val();
            $.post('{{ route('orders.update_payment_status') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                status: status
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Payment status has been updated') }}');
            });
        });
        $('#update_tracking_code').on('change', function() {
            var order_id = {{ $order->id }};
            var tracking_code = $('#update_tracking_code').val();
            $.post('{{ route('orders.update_tracking_code') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: order_id,
                tracking_code: tracking_code
            }, function(data) {
                AIZ.plugins.notify('success', '{{ translate('Order tracking code has been updated') }}');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.livesearch').select2({
                placeholder: 'Select Product',
                ajax: {
                    url: '{{route('ajax-autocomplete-search')}}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        console.log(data);

                        return {
                            results: $.map(data, function (item) {
                                // console.log(item.id)
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $(document).on('change','.livesearch',function () {
                var cat_id=$(this).val();
                console.log(cat_id);

                div=$('.productsize').parent();

                var op=" ";

                $.ajax({
                    type:'get',
                    url:'{{route('selectProductSize')}}',
                    data:{'id':cat_id},
                    success:function(data){
                        //console.log('success');

                        console.log(data);

                        //console.log(data.length);
                        op+='<option value="0" selected disabled>Chose size</option>';
                        for(var i=0;i<data.length;i++){
                            op+='<option value="'+data[i].id+'">'+data[i].variant+' - '+data[i].price+' </option>';
                        }

                        div.find('.productsize').html(" ");
                        div.find('.productsize').append(op);
                    },
                    error:function(){

                    }
                });
            });

            $('#add-item').click(function(e) {
                e. preventDefault();
                //setting variables based on the input fields
                var idProduct = $('.livesearch').val();
                var idVariant = $('.productsize').val();
                var idOrder = {{ $order->id }}
                console.log(idOrder)
                {{--  var inputDrink = $('input[name="drink"]').val();
                var token = $('input[name="_token"]').val(); --}}
                var data = {idProduct:idProduct, idVariant:idVariant, idOrder:idOrder};

                var request = $.ajax({
                    // url: '/oos/item/add/'+idVariant+'/'+idProduct+'/'+idOrder+'',
                    url: '{{ route('item.add.order', ['id' => ':idVariant', 'product' => ':idProduct', 'order' => ':idOrder']) }}'.replace(':idVariant', idVariant).replace(':idProduct', idProduct).replace(':idOrder', idOrder),
                    type: "GET",
                    data: data,
                    dataType:"html"
                    });

                    request.done(function( msg ) {
                        var response = JSON.parse(msg);
                        console.log(response.msg);
                        location.reload();
                    });

                    request.fail(function( jqXHR, textStatus ) {
                        console.log( "Request failed: " + testStatus );
                    });
            });

        });
    </script>
@endsection
