@extends('frontend.layouts.app')

@section('content')
<section class="mb-4 pt-5">
    <div class="container">
        <div class="row">
            @php
                $coupon_discount = 0;
            @endphp
            @php
                $delivery_info_step_active = get_setting('delivery_info_step_status');
            @endphp
            @if (get_setting('coupon_system') == 1)
                @php
                    $coupon_code = null;
                @endphp

                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = \App\Models\Product::find($cartItem['product_id']);
                    @endphp
                    @if ($cartItem->coupon_applied == 1)
                        @php
                            $coupon_code = $cartItem->coupon_code;
                            break;
                        @endphp
                    @endif
                @endforeach

                @php
                    $coupon_discount = carts_coupon_discount($coupon_code, false, true);
                @endphp
            @endif

            @php $subtotal_for_min_order_amount = 0; @endphp
            @foreach ($carts as $key => $cartItem)
                @php $subtotal_for_min_order_amount += cart_product_price($cartItem, $cartItem->product, false, false) * $cartItem['quantity']; @endphp
            @endforeach
            @php
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    $product_shipping_cost = 0;
                @endphp
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = \App\Models\Product::find($cartItem['product_id']);
                        $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $product_shipping_cost = $cartItem['shipping_cost'];
                        $addons_total = 0;

                        $shipping += $product_shipping_cost;

                        $product_name_with_choice = $product->getTranslation('name');
                        if ($cartItem['variant'] != null) {
                            $product_name_with_choice = $product->getTranslation('name') . ' - ' . $cartItem['variant'];
                        }
                        if($cartItem['addons'] != null || $cartItem['addons'] != "") {
                            foreach(json_decode($cartItem['addons']) as $addon){
                                $subtotal += $addon->price*(int)$addon->quantity;
                                $addons_total += $addon->price*(int)$addon->quantity;
                            }
                        }
                    @endphp
                @endforeach
                @php
                    $total = $subtotal+$tax+$shipping;
                    if ($carts->sum('discount') > 0){
                        $total -= $carts->sum('discount');
                    }
                @endphp
            <div class="col-xl-8 mx-auto">
                <div class="row aiz-steps arrow-divider">
                    <div class="col done">
                        <div class="text-success text-center">
                            <i class="la-3x las la-shopping-cart mb-2"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('1. My Cart') }}</h3>
                        </div>
                    </div>
                    <div class="col done">
                        <div class="text-success text-center">
                            <i class="la-3x las la-map mb-2"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('2. Shipping info') }}</h3>
                        </div>
                    </div>
                    @if($delivery_info_step_active == true)
                        <div class="col done">
                            <div class="text-success text-center">
                                <i class="la-3x las la-truck mb-2"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block">{{ translate('3. Delivery info') }}</h3>
                            </div>
                        </div>
                    @endif
                    <div class="col active">
                        <div class="text-primary text-center">
                            <i class="la-3x las la-credit-card mb-2"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block">{{ $delivery_info_step_active == true ? translate('4. Payment') : translate('3. Payment') }}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <i class="la-3x las la-check-circle mb-2 opacity-50"></i>
                            <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ $delivery_info_step_active == true ? translate('5. Confirmation') : translate('4. Confirmation') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container text-left">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('payment.checkout_guest') }}" class="form-default" role="form" method="POST" id="checkout-form">
                    @csrf
                    <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">

                    <div class="card shadow-sm border-0 rounded">
                        <div class="card-header p-3">
                            <h3 class="fs-16 fw-600 mb-0">
                                {{ translate('Select a payment option')}}
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="row">
                                <div class="col-xxl-8 col-xl-10 mx-auto">
                                    <div class="row gutters-10">
                                        @if (get_setting('tabby_payment') == 1 && $total >= get_setting('TABBY_MIN_AMOUNT') && $total <= get_setting('TABBY_MAX_AMOUNT'))
                                        <div class="col-6 col-md-4">
                                            <label class="aiz-megabox d-block mb-3">
                                                <input value="tabby_payment" class="online_payment" type="radio"
                                                    name="payment_option">
                                                <span class="d-block aiz-megabox-elem p-3">
                                                    <img src="{{ static_asset('assets/img/cards/tabby_payment.png') }}"
                                                        class="img-fluid mb-2">
                                                    <span class="d-block text-center">
                                                        <span
                                                            class="d-block fw-600 fs-15">{{ translate('Tabby payment') }}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    @endif
                                        @if(get_setting('paypal_payment') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="paypal" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/paypal.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Paypal')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('stripe_payment') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="stripe" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/stripe.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Stripe')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('sslcommerz_payment') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="sslcommerz" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/sslcommerz.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('sslcommerz')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('instamojo_payment') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="instamojo" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/instamojo.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Instamojo')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('razorpay') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="razorpay" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/rozarpay.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Razorpay')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('paystack') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="paystack" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/paystack.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Paystack')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('voguepay') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="voguepay" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/vogue.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('VoguePay')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('payhere') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="payhere" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/payhere.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('payhere')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('ngenius') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="ngenius" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/ngenius.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('ngenius')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('iyzico') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="iyzico" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/iyzico.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Iyzico')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('nagad') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="nagad" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/nagad.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Nagad')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('bkash') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="bkash" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/bkash.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Bkash')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('aamarpay') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="aamarpay" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/aamarpay.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Aamarpay')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('proxypay') == 1)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="proxypay" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/proxypay.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('ProxyPay')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                            @if (get_setting('bookeeypay') == 1)
                                                <div class="col-6 col-md-4">
                                                    <label class="aiz-megabox d-block mb-3">
                                                        <input value="bookeeypay" class="online_payment" type="radio"
                                                            name="payment_option">
                                                        <span class="d-block aiz-megabox-elem p-3">
                                                            <img src="{{ static_asset('assets/img/cards/logo-knet.webp') }}" style="width: 50px; height: 50px;"
                                                                class="img-fluid mb-2">
                                                            <span class="d-block text-center">
                                                                <span
                                                                    class="d-block fw-600 fs-15">{{ translate('Knet') }}</span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <label class="aiz-megabox d-block mb-3">
                                                        <input value="bookeeypay_credit" class="online_payment" type="radio"
                                                            name="payment_option">
                                                        <span class="d-block aiz-megabox-elem p-3">
                                                            <img src="{{ static_asset('assets/img/cards/visa_american.svg') }}" style="width: 50px; height: 50px;"
                                                                class="img-fluid mb-2">
                                                            <span class="d-block text-center">
                                                                <span
                                                                    class="d-block fw-600 fs-15">{{ translate('Visa / Master') }}</span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endif
                                            @if(get_setting('upayment') == 1)
                                                @if(get_setting('upayment_kenet') == 1)
                                                    <div class="col-6 col-md-4">
                                                        <label class="aiz-megabox d-block mb-3">
                                                            <input value="upay" class="online_payment" type="radio" name="payment_option" checked>
                                                            <span class="d-block p-3 aiz-megabox-elem">
                                                                <img src="{{ static_asset('assets/img/cards/upayment-icon.svg')}}" style="height: 50px" class="img-fluid mb-2">
                                                                <span class="d-block text-center">
                                                                    <span class="d-block fw-600 fs-15">{{ translate('Online payment')}}</span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                                @if(get_setting('upayment_visa') == 1)
                                                    <div class="col-6 col-md-4">
                                                        <label class="aiz-megabox d-block mb-3">
                                                            <input value="upay_credit" class="online_payment" type="radio" name="payment_option" checked>
                                                            <span class="d-block p-3 aiz-megabox-elem">
                                                                <img src="{{ static_asset('assets/img/cards/visa_american.svg')}}" class="img-fluid mb-2">
                                                                <span class="d-block text-center">
                                                                    <span class="d-block fw-600 fs-15">{{ translate('Credit')}}</span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endif
                                            @endif
                                        @if(\App\Models\Addon::where('unique_identifier', 'african_pg')->first() != null && \App\Models\Addon::where('unique_identifier', 'african_pg')->first()->activated)
                                            @if(get_setting('mpesa') == 1)
                                                <div class="col-6 col-md-4">
                                                    <label class="aiz-megabox d-block mb-3">
                                                        <input value="mpesa" class="online_payment" type="radio" name="payment_option" checked>
                                                        <span class="d-block p-3 aiz-megabox-elem">
                                                            <img src="{{ static_asset('assets/img/cards/mpesa.png')}}" class="img-fluid mb-2">
                                                            <span class="d-block text-center">
                                                                <span class="d-block fw-600 fs-15">{{ translate('mpesa')}}</span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endif
                                            @if(get_setting('flutterwave') == 1)
                                                <div class="col-6 col-md-4">
                                                    <label class="aiz-megabox d-block mb-3">
                                                        <input value="flutterwave" class="online_payment" type="radio" name="payment_option" checked>
                                                        <span class="d-block p-3 aiz-megabox-elem">
                                                            <img src="{{ static_asset('assets/img/cards/flutterwave.png')}}" class="img-fluid mb-2">
                                                            <span class="d-block text-center">
                                                                <span class="d-block fw-600 fs-15">{{ translate('flutterwave')}}</span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endif
                                            @if(get_setting('payfast') == 1)
                                                <div class="col-6 col-md-4">
                                                    <label class="aiz-megabox d-block mb-3">
                                                        <input value="payfast" class="online_payment" type="radio" name="payment_option" checked>
                                                        <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/payfast.png')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('payfast')}}</span>
                                                        </span>
                                                    </span>
                                                    </label>
                                                </div>
                                            @endif
                                        @endif
                                        @if(\App\Models\Addon::where('unique_identifier', 'paytm')->first() != null && \App\Models\Addon::where('unique_identifier', 'paytm')->first()->activated)
                                            <div class="col-6 col-md-4">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="paytm" class="online_payment" type="radio" name="payment_option" checked>
                                                    <span class="d-block p-3 aiz-megabox-elem">
                                                        <img src="{{ static_asset('assets/img/cards/paytm.jpg')}}" class="img-fluid mb-2">
                                                        <span class="d-block text-center">
                                                            <span class="d-block fw-600 fs-15">{{ translate('Paytm')}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        @endif
                                        @if(get_setting('cash_payment') == 1)
                                            @php
                                                $digital = 0;
                                                $cod_on = 1;
                                                foreach($carts as $cartItem){
                                                    $product = \App\Models\Product::find($cartItem['product_id']);
                                                    if($product['digital'] == 1){
                                                        $digital = 1;
                                                    }
                                                    if($product['cash_on_delivery'] == 0){
                                                        $cod_on = 0;
                                                    }
                                                }
                                            @endphp
                                            @if($digital != 1 && $cod_on == 1)
                                                <div class="col-6 col-md-4">
                                                        <label class="aiz-megabox d-block mb-3">
                                                            <input value="cash_on_delivery" class="online_payment"
                                                                type="radio" name="payment_option">
                                                            <span class="d-block aiz-megabox-elem p-3">
                                                                <img src="{{ static_asset('assets/img/cards/logo-cash.svg') }}" style="
    width: 50px;
    height: 50px;
"
                                                                    class="img-fluid mb-2">
                                                                <span class="d-block text-center">
                                                                    <span
                                                                        class="d-block fw-600 fs-15">{{ translate('Cash on Delivery') }}</span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                    </div>
                                            @endif
                                        @endif
                                        @if (Auth::check())
                                            @if (\App\Models\Addon::where('unique_identifier', 'offline_payment')->first() != null && \App\Models\Addon::where('unique_identifier', 'offline_payment')->first()->activated)
                                                @foreach(\App\Models\ManualPaymentMethod::all() as $method)
                                                    <div class="col-6 col-md-4">
                                                        <label class="aiz-megabox d-block mb-3">
                                                            <input value="{{ $method->heading }}" type="radio" name="payment_option" onchange="toggleManualPaymentData({{ $method->id }})" data-id="{{ $method->id }}" checked>
                                                            <span class="d-block p-3 aiz-megabox-elem">
                                                                <img src="{{ uploaded_asset($method->photo) }}" class="img-fluid mb-2">
                                                                <span class="d-block text-center">
                                                                    <span class="d-block fw-600 fs-15">{{ $method->heading }}</span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endforeach

                                                @foreach(\App\Models\ManualPaymentMethod::all() as $method)
                                                    <div id="manual_payment_info_{{ $method->id }}" class="d-none">
                                                        @php echo $method->description @endphp
                                                        @if ($method->bank_info != null)
                                                            <ul>
                                                                @foreach (json_decode($method->bank_info) as $key => $info)
                                                                    <li>{{ translate('Bank Name') }} - {{ $info->bank_name }}, {{ translate('Account Name') }} - {{ $info->account_name }}, {{ translate('Account Number') }} - {{ $info->account_number}}, {{ translate('Routing Number') }} - {{ $info->routing_number }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if (\App\Models\Addon::where('unique_identifier', 'offline_payment')->first() != null && \App\Models\Addon::where('unique_identifier', 'offline_payment')->first()->activated)
                                <div class="bg-white border mb-3 p-3 rounded text-left d-none">
                                    <div id="manual_payment_description">

                                    </div>
                                </div>
                            @endif
                            @if (get_setting('tabby_payment') == 1 && $total >= get_setting('TABBY_MIN_AMOUNT') && $total <= get_setting('TABBY_MAX_AMOUNT'))
                                <div class="card rounded border-0 shadow-sm mb-4" id="tabby-payment-options" style="display: none;">
                                <div class="card rounded border-0 shadow-sm mb-4">
                                    <div class="card-header p-3">
                                        <h3 class="fs-16 fw-600 mb-0">
                                            {{ translate('Flexible Payment Options') }}
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="tabby-payment-options">
                                                    <!-- Pay in 4 -->
                                                    <div class="tabby-option mb-3 p-3 border rounded">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <img src="{{ static_asset('assets/img/cards/tabby_payment.png') }}"
                                                                style="height: 24px"
                                                                class="me-2"
                                                                alt="Tabby">
                                                            <h5 class="mb-0 fw-600">{{ translate('Pay in 4') }}</h5>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span>{{ translate('Today:') }}</span>
                                                            <span class="fw-600" id="tabby-today-payment">{{ single_price($total/4) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span>{{ translate('Next 3 payments:') }}</span>
                                                            <span class="fw-600" id="tabby-next-payments">{{ single_price($total/4) }} x 3</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>{{ translate('Total:') }}</span>
                                                            <span class="fw-600" id="tabby-total-payment">{{ single_price($total) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if (Auth::check() && get_setting('wallet_system') == 1)
                                <div class="separator mb-3">
                                    <span class="bg-white px-3">
                                        <span class="opacity-60">{{ translate('Or')}}</span>
                                    </span>
                                </div>
                                <div class="text-center py-4">
                                    <div class="h6 mb-3">
                                        <span class="opacity-80">{{ translate('Your wallet balance :')}}</span>
                                        <span class="fw-600">{{ single_price(Auth::user()->balance) }}</span>
                                    </div>
                                    @if(Auth::user()->balance < $total)
                                        <button type="button" class="btn btn-secondary" disabled>
                                            {{ translate('Insufficient balance')}}
                                        </button>
                                    @else
                                        <button  type="button" onclick="use_wallet()" class="btn btn-primary fw-600">
                                            {{ translate('Pay with wallet')}}
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="pt-3">
                        <label class="aiz-checkbox">
                            <input type="checkbox" required id="agree_checkbox">
                            <span class="aiz-square-check"></span>
                            <span>{{ translate('I agree to the')}}</span>
                        </label>
                        <a href="{{ route('terms') }}">{{ translate('terms and conditions')}}</a>,
                        <a href="{{ route('returnpolicy') }}">{{ translate('return policy')}}</a> &
                        <a href="{{ route('privacypolicy') }}">{{ translate('privacy policy')}}</a>
                    </div>

                    <div class="row align-items-center p-4">
                        <div class="col-6">
                            <a href="{{ route('home') }}" class="link link--style-3">
                                <i class="las la-arrow-left"></i>
                                {{ translate('Return to shop')}}
                            </a>
                        </div>
                        <div class="col-6 text-right">
                            <button type="button" onclick="submitOrder(this)" class="btn btn-primary fw-600">{{ translate('Complete Order')}}</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0" id="cart_summary_guest">
                @include('frontend.partials.cart_summary_guest')
            </div>
        </div>
    </div>
</section>
@endsection
@section('style')
    <style>
        .tabby-payment-options .tabby-option {
    transition: all 0.3s ease;
    background-color: #f9f9f9;
}

.tabby-payment-options .tabby-option:hover {
    background-color: #f0f7ff;
    border-color: #0066cc;
}

.tabby-payment-options h5 {
    font-size: 15px;
}
    </style>
    @endsection


@section('script')
    <script type="text/javascript">

        $(document).ready(function(){
            $(".online_payment").click(function(){
                $('#manual_payment_description').parent().addClass('d-none');
            });
            toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));
        });

        function use_wallet(){
            $('input[name=payment_option]').val('wallet');
            if($('#agree_checkbox').is(":checked")){
                $('#checkout-form').submit();
            }else{
                AIZ.plugins.notify('danger','{{ translate('You need to agree with our policies') }}');
            }
        }
        function submitOrder(el){
            $(el).prop('disabled', true);
            if($('#agree_checkbox').is(":checked")){
                var payment_option = $('input[name=payment_option]:checked').val();
                console.log(payment_option);
                if(payment_option == undefined){
                    AIZ.plugins.notify('danger', '{{ translate('Please select a payment option') }}');
                    $(el).prop('disabled', false);
                    return;
                }
                $('#checkout-form').submit();
            }else{
                AIZ.plugins.notify('danger','{{ translate('You need to agree with our policies') }}');
                $(el).prop('disabled', false);
            }
        }

        function toggleManualPaymentData(id){
            if(typeof id != 'undefined'){
                $('#manual_payment_description').parent().removeClass('d-none');
                $('#manual_payment_description').html($('#manual_payment_info_'+id).html());
            }
        }

        $(document).on("click", "#coupon-apply",function() {
            var data = new FormData($('#apply-coupon-form')[0]);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: "{{route('checkout.apply_coupon_code_guest')}}",
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                error: function (jqXHR, exception) {
                    if (jqXHR.status == 419) {
                        AIZ.plugins.notify('danger', '{{ translate('Your session has expired, please login again') }}');
                    }
                    else {
                        AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    }
                },
                success: function(data, textStatus, jqXHR) {
                    $("#cart_summary_guest").html(data.html);
                    AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                    var newTotalText = $(data.html).find('.cart-total strong span').text();
                    var newTotal = parseFloat(newTotalText.replace(/[^0-9.-]+/g,""));
                    updateTabbyPaymentOptions(newTotal);
                    // var data = new FormData($('#apply-coupon-form')[0]);

                    // $.ajax({
                    //     headers: {
                    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    //     },
                    //     method: "POST",
                    //     url: "{{route('checkout.apply_coupon_code_guest')}}",
                    //     data: data,
                    //     cache: false,
                    //     contentType: false,
                    //     processData: false,
                    //     success: function(data, textStatus, jqXHR) {
                    //         $("#cart_summary_guest").html(data.html);
                    //     }})
                        }
                    })
        });

        $(document).on("click", "#coupon-remove",function() {
            var data = new FormData($('#remove-coupon-form')[0]);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: "{{route('checkout.remove_coupon_code_guest')}}",
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data, textStatus, jqXHR) {
                    $("#cart_summary_guest").html(data);
                    var newTotalText = $(data).find('.cart-total strong span').text();
                    var newTotal = parseFloat(newTotalText.replace(/[^0-9.-]+/g,""));

                    updateTabbyPaymentOptions(newTotal);
                }
            })
        })

        function updateTabbyPaymentOptions(total) {
            var todayPayment = (total / 4).toFixed(2);
            var formattedTodayPayment = '{{ currency_symbol() }}' + todayPayment;
            var formattedTotal = '{{ currency_symbol() }}' + total.toFixed(2);

            $('#tabby-today-payment').text(formattedTodayPayment);
            $('#tabby-next-payments').text(formattedTodayPayment + ' x 3');
            $('#tabby-total-payment').text(formattedTotal);

            var tabbyMin = parseFloat('{{ get_setting("TABBY_MIN_AMOUNT") }}');
            var tabbyMax = parseFloat('{{ get_setting("TABBY_MAX_AMOUNT") }}');

            if (total >= tabbyMin && total <= tabbyMax) {
                // Only show if Tabby payment is selected
                if ($('input[name="payment_option"]:checked').val() === 'tabby_payment') {
                    $('#tabby-payment-options').show();
                }
            } else {
                $('#tabby-payment-options').hide();
            }
        }

        $(document).ready(function() {
            var initialTotalText = $('#cart_summary_guest').find('.cart-total strong span').text();
            var initialTotal = parseFloat(initialTotalText.replace(/[^0-9.-]+/g,""));
            updateTabbyPaymentOptions(initialTotal);

            $('input[name="payment_option"]').change(function() {
                if ($(this).val() === 'tabby_payment') {
                    // $('#tabby-payment-options').show();
                    $('.tabby-option').addClass('bg-primary-light border-primary');
                } else {
                    $('#tabby-payment-options').hide();
                    $('.tabby-option').removeClass('bg-primary-light border-primary');
                }
            });

            if ($('input[name="payment_option"]:checked').val() === 'tabby_payment') {
                $('#tabby-payment-options').show();
                $('.tabby-option').addClass('bg-primary-light border-primary');
            }
        });
    </script>
    <script>
        $(document).on('change', 'input[name="payment_option"]', function() {
        if($(this).val() === 'tabby_payment') {
            $('.tabby-option').addClass('bg-primary-light border-primary');
        } else {
            $('.tabby-option').removeClass('bg-primary-light border-primary');
        }
    });
        </script>
        <script>
            $(document).ready(function() {
                var initialTotal = parseFloat('{{ $total }}');
                updateTabbyPaymentOptions(initialTotal);

                $('input[name="payment_option"]').change(function() {
                    if ($(this).val() === 'tabby_payment') {
                        $('#tabby-payment-options').show();
                        $('.tabby-option').addClass('bg-primary-light border-primary');
                    } else {
                        $('#tabby-payment-options').hide();
                        $('.tabby-option').removeClass('bg-primary-light border-primary');
                    }
                });

                if ($('input[name="payment_option"]:checked').val() === 'tabby_payment') {
                    $('#tabby-payment-options').show();
                    $('.tabby-option').addClass('bg-primary-light border-primary');
                }
            });
        </script>
@endsection
