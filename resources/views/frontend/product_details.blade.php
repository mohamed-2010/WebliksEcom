@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->getTranslation('meta_title')}}@stop

@section('meta_description'){{ $detailedProduct->getTranslation('meta_description') }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->getTranslation('meta_title') }}">
    <meta itemprop="description" content="{{ $detailedProduct->getTranslation('meta_description') }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->getTranslation('meta_title') }}">
    <meta name="twitter:description" content="{{ $detailedProduct->getTranslation('meta_description') }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->getTranslation('meta_title')}}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->getTranslation('meta_description') }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:price:currency" content="{{ \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code }}" />
    <meta property="og:price:currency" content="{{ \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
@endsection

@section('content')
    <section class="mb-4 pt-3">
        <div class="container">
            <div class="bg-white shadow-sm rounded p-3">
                <div class="row">
                    <div class="col-xl-5 col-lg-6 mb-4">
                        <!-- Removed gutters-10 to solve stack of texts -->
                        <div class="sticky-top z-3 col-reverse">
                            @php
                                $photos = explode(',', $detailedProduct->photos);
                            @endphp
                            <div class="col order-1 order-md-2">
                                <div class="aiz-carousel product-gallery" data-nav-for='.product-gallery-thumb'
                                    data-fade='true' data-auto-height='false'>
                                    @foreach ($photos as $key => $photo)
                                        <div class="carousel-box img-zoom rounded">
                                            <img class="img-fluid lazyload"
                                                src="{{ asset('public/assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ asset('public/assets/img/placeholder.jpg') }}';">
                                        </div>
                                    @endforeach
                                    @foreach ($detailedProduct->stocks as $key => $stock)
                                        @if ($stock->image != null)
                                            <div class="carousel-box img-zoom rounded">
                                                <img class="img-fluid lazyload"
                                                    src="{{ asset('public/assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($stock->image) }}"
                                                    onerror="this.onerror=null;this.src='{{ asset('public/assets/img/placeholder.jpg') }}';">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 col-md-auto order-2 order-md-1 mt-3 mt-md-0">
                                <div class="aiz-carousel product-gallery-thumb" data-items='5'
                                    data-nav-for='.product-gallery' data-vertical='false' data-vertical-xl='false'
                                    data-focus-select='true' data-arrows='true'>
                                    @foreach ($photos as $key => $photo)
                                        <div class="carousel-box c-pointer border p-1 rounded">
                                            <img class="lazyload mw-100 size-50px mx-auto"
                                                src="{{ asset('public/assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ asset('public/assets/img/placeholder.jpg') }}';">
                                        </div>
                                    @endforeach
                                    @foreach ($detailedProduct->stocks as $key => $stock)
                                        @if ($stock->image != null)
                                            <div class="carousel-box c-pointer border p-1 rounded"
                                                data-variation="{{ $stock->variant }}">
                                                <img class="lazyload mw-100 size-50px mx-auto"
                                                    src="{{ asset('public/assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($stock->image) }}"
                                                    onerror="this.onerror=null;this.src='{{ asset('public/assets/img/placeholder.jpg') }}';">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7 col-lg-6">
                        <div class="text-left">
                            <h1 class="mb-2 fs-20 fw-600">
                                {{ $detailedProduct->getTranslation('name') }}
                            </h1>

                            <div class="row align-items-center">
                                <div class="col-12">
                                    @php
                                        $total = 0;
                                        $total += $detailedProduct->reviews->count();
                                    @endphp
                                    <span class="rating">
                                        {{ renderStarRating($detailedProduct->rating) }}
                                    </span>
                                    <span class="ml-1 opacity-50">({{ $total }}
                                        {{ translate('reviews') }})</span>
                                </div>
                                @if ($detailedProduct->est_shipping_days)
                                    <div class="col-auto ml">
                                        <small class="mr-2 opacity-50">{{ translate('Estimate Shipping Time') }}:
                                        </small>{{ $detailedProduct->est_shipping_days }} {{ translate('Days') }}
                                    </div>
                                @endif
                            </div>

                            <!--<hr>-->

                            <div class="row align-items-center">
                                <!--Ahmed Mousa-->
                                <!--<div class="col-auto">-->
                                <!--    <small class="mr-2 opacity-50">{{ translate('Sold by') }}: </small><br>-->
                                <!--    @if ($detailedProduct->added_by == 'seller' && get_setting('vendor_system_activation') == 1)-->
                                <!--        <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}"-->
                                <!--            class="text-reset">{{ $detailedProduct->user->shop->name }}</a>-->
                                <!--    @else-->
                                <!--        {{ translate('Inhouse product') }}-->
                                <!--    @endif-->
                                <!--</div>-->
                                <!--@if (get_setting('conversation_system') == 1)-->
                                <!--    <div class="col-auto">-->
                                <!--        <button class="btn btn-sm btn-soft-primary"-->
                                <!--            onclick="show_chat_modal()">{{ translate('Message Seller') }}</button>-->
                                <!--    </div>-->
                                <!--@endif-->

                                @if ($detailedProduct->brand != null)
                                    <div class="col-auto">
                                        <a href="{{ route('products.brand', $detailedProduct->brand->slug) }}">
                                            <img src="{{ uploaded_asset($detailedProduct->brand->logo) }}"
                                                alt="{{ $detailedProduct->brand->getTranslation('name') }}"
                                                height="30">
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <hr>

                                @php
                                //final product price calculation
                                $tax = 0;
                                $discount = 0;
                                $taxes = array();
                                foreach ($detailedProduct->taxes as $tax) {
                                    if ($tax->tax_type == 'percent') {
                                        array_push($taxes, $tax->tax);
                                    }
                                }
                                if (count($taxes) > 0) {
                                    $tax = array_sum($taxes);
                                }
                                //if ($detailedProduct->discount != null) {
                                //    $discount = $detailedProduct->discount->discount;
                                //}

                                $flash_deal = \App\Models\FlashDeal::where('status', 1)->where('featured', 1)->first();
                                $flash_price = null;
                                $flash_discount = 0;
                                if ($flash_deal != null) {
                                    $flash_price = $detailedProduct->unit_price;
                                    $flash_discount = $detailedProduct->discount;
                                    if ($detailedProduct->discount_type == 'percent') {
                                        $flash_price -= ($detailedProduct->discount * $flash_price) / 100;
                                    } elseif ($detailedProduct->discount_type == 'amount') {
                                        $flash_price -= $detailedProduct->discount;
                                    }
                                }

                                $base_price = $detailedProduct->unit_price;
                                $base_discount = $discount;
                                if ($detailedProduct->discount_type == 'percent') {
                                    $base_price -= ($detailedProduct->discount * $base_price) / 100;
                                } elseif ($detailedProduct->discount_type == 'amount') {
                                    $base_price -= $detailedProduct->discount;
                                }

                                $final_price = $base_price + $tax;
                            @endphp


                            @if ($detailedProduct->wholesale_product)
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Min Qty') }}</th>
                                            <th>{{ translate('Max Qty') }}</th>
                                            <th>{{ translate('Unit Price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($detailedProduct->stocks->first()->wholesalePrices as $wholesalePrice)
                                            <tr>
                                                <td>{{ $wholesalePrice->min_qty }}</td>
                                                <td>{{ $wholesalePrice->max_qty }}</td>
                                                <td>{{ single_price($wholesalePrice->price) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="row no-gutters mt-3">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ translate('Sku') }}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="fs-20 opacity-60" id="sku">
                                        </div>
                                    </div>
                                </div>
                                @if (home_price($detailedProduct) != home_discounted_price($detailedProduct))

                                    <div class="row no-gutters mt-3">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ translate('Price') }}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="fs-20 opacity-60">
                                                <del>
                                                    {{ home_price($detailedProduct) }}
                                                </del>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row no-gutters my-2">
                                        <div class="col-sm-2">
                                            <div class="opacity-50">{{ translate('Discount Price') }}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ home_discounted_price($detailedProduct) }}
                                                </strong>
                                                @if ($detailedProduct->unit != null)
                                                <!--Ahmed Mousa-->
                                                    <!--<span-->
                                                    <!--    class="opacity-70">/{{ $detailedProduct->getTranslation('unit') }}</span>-->
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="row no-gutters mt-3">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ translate('Price') }}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="">
                                                <strong class="h2 fw-600 text-primary">
                                                    {{ home_discounted_price($detailedProduct) }}
                                                </strong>
                                                @if ($detailedProduct->unit != null)
                                                <!--Ahmed Mousa-->
                                                    <!--<span-->
                                                    <!--    class="opacity-70">/{{ $detailedProduct->getTranslation('unit') }}</span>-->
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                                <div class="row no-gutters mt-4">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ translate('Club Point') }}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div
                                            class="d-inline-block rounded px-2 bg-soft-primary border-soft-primary border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif




                            <hr>



                            <form id="option-choice-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $detailedProduct->id }}">

                                @if ($detailedProduct->choice_options != null)
                                    @foreach (json_decode($detailedProduct->choice_options) as $key => $choice)
                                        <div class="row no-gutters">
                                            <div class="col-sm-2">
                                                <div class="opacity-50 my-2">
                                                    {{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}:
                                                </div>
                                            </div>
                                            <div class="col-sm-10">
                                                @if (count($choice->values) > 3)
                                                    <select class="form-control aiz-selectpicker" name="attribute_id_{{ $choice->attribute_id }}">
                                                        <option value="">Choose {{ \App\Models\Attribute::find($choice->attribute_id)->getTranslation('name') }}</option>
                                                        @foreach ($choice->values as $value)
                                                            <option value="{{ $value }}">
                                                                {{ \App\Models\AttributeValue::where('value', $value)->first()->get_translation('value') ?? $value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <div class="aiz-radio-inline">
                                                        @foreach ($choice->values as $key => $value)
                                                            <label class="aiz-megabox pl-0 mr-2">
                                                                <input type="radio"
                                                                    name="attribute_id_{{ $choice->attribute_id }}"
                                                                    value="{{ $value }}"
                                                                    {{--@if ($key == 0) checked @endif--}}>
                                                                <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-2 px-3 mb-2">
                                                                    {{ \App\Models\AttributeValue::where('value', $value)->first()->get_translation('value') ?? $value }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                @if (count(json_decode($detailedProduct->colors)) > 0)
                                    <div class="row no-gutters">
                                        <div class="col-sm-2">
                                            <div class="opacity-50 my-2">{{ translate('Color') }}:</div>
                                        </div>
                                        <div class="col-sm-10">
                                            <div class="aiz-radio-inline">
                                                @foreach (json_decode($detailedProduct->colors) as $key => $color)
                                                    <label class="aiz-megabox pl-0 mr-2" data-toggle="tooltip"
                                                        data-title="{{ \App\Models\Color::where('code', $color)->first()->name }}">
                                                        <input type="radio" name="color"
                                                            value="{{ \App\Models\Color::where('code', $color)->first()->name }}"
                                                            {{--@if ($key == 0) checked @endif--}}>
                                                        <span
                                                            class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center p-1 mb-2">
                                                            <span class="size-30px d-inline-block rounded"
                                                                style="background: {{ $color }};"></span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                @endif

                                @if( $detailedProduct->category_add_on_ids != 'null' && $detailedProduct->category_add_on_ids != "" && count(json_decode($detailedProduct->category_add_on_ids)) > 0 )

                                    <div class="">
                                        <h5 class="opacity-50">{{ translate('Addons') }}:</h5>
                                    </div>
                                    <div class="row d-flex flex-wrap ml-3">
                                        @foreach (json_decode($detailedProduct->category_add_on_ids) as $key => $category_add_on_id)
                                            @php
                                                $category_add_on = \App\Models\CategoryAddon::find($category_add_on_id);
                                                //$product_addons = \App\Models\ProductAddon::where('category_add_on_id', $category_add_on_id)->get();
                                            @endphp

                                            @if ($category_add_on != null)
                                            <div class="row">
                                                <div class="row no-gutters">
                                                    <div class="col-sm-12 px-3">
                                                        <div class="aiz-radio-inline">
                                                                <label class="aiz-megabox pl-0 mr-2">
                                                                    <input name="product_addon_id_{{ $category_add_on->id }}" id="product_addon_id_{{ $category_add_on->id }}"
                                                                        value="0">
                                                                    <span class="aiz-megabox-elem rounded d-flex align-items-center justify-content-center py-1 px-3 mb-2">
                                                                        {{ $category_add_on->getTranslation('name') }}
                                                                        <div style="width: 10px;"></div>
                                                                        <!-- Quantity of addon -->
                                                                        <div class="row quantity-input items-center justify-center" style="align-items: center">
                                                                            <!-- Quantity of addon with price -->
                                                                            @if ($category_add_on->price != null)
                                                                                <span class="ml-2 text-warning" id="price_addon_{{ $category_add_on->id }}" data-price="{{ $category_add_on->price }}" data-addon-id="{{$category_add_on->id}}">
                                                                                    (+{{ single_price($category_add_on->price) }})
                                                                                </span>

                                                                            @endif
                                                                            <div class="col">
                                                                                <button class="decrease btn col-auto btn-icon btn-sm btn-circle btn-light">-</button>
                                                                                <input type="text" value="0" id="quant_addon_{{ $category_add_on->id }}" hidden>
                                                                                <span id="quant_addon_{{ $category_add_on->id }}_h5">0</span>
                                                                                <button class="increase btn col-auto btn-icon btn-sm btn-circle btn-light">+</button>
                                                                            </div>
                                                                        </div>
                                                                    </span>
                                                                </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>

                                @endif

                                <!-- Quantity + Add to cart -->
                                <div class="row no-gutters">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ translate('Quantity') }}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="product-quantity d-flex align-items-center">
                                            <div class="row no-gutters align-items-center aiz-plus-minus mr-3"
                                                style="width: 130px;">
                                                <button class="btn col-auto btn-icon btn-sm btn-circle btn-light"
                                                    type="button" data-type="minus" data-field="quantity"
                                                    disabled="">
                                                    <i class="las la-minus"></i>
                                                </button>
                                                <input type="number" name="quantity"
                                                    class="col border-0 text-center flex-grow-1 fs-16 input-number"
                                                    placeholder="1" value="{{ $detailedProduct->min_qty }}"
                                                    min="{{ $detailedProduct->min_qty }}" max="10"
                                                    lang="en">
                                                <button class="btn  col-auto btn-icon btn-sm btn-circle btn-light"
                                                    type="button" data-type="plus" data-field="quantity">
                                                    <i class="las la-plus"></i>
                                                </button>
                                            </div>
                                            @php
                                                $qty = 0;
                                                foreach ($detailedProduct->stocks as $key => $stock) {
                                                    $qty += $stock->qty;
                                                }
                                            @endphp
                                            <div class="avialable-amount opacity-60">
                                                @if ($detailedProduct->stock_visibility_state == 'quantity')
                                                    (<span id="available-quantity">{{ $qty }}</span>
                                                    {{ translate('available') }})
                                                @elseif($detailedProduct->stock_visibility_state == 'text' && $qty >= 1)
                                                    (<span id="available-quantity">{{ translate('In Stock') }}</span>)
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row no-gutters pb-3 d-none" id="chosen_price_div">
                                    <div class="col-sm-2">
                                        <div class="opacity-50 my-2">{{ translate('Total Price') }}:</div>
                                    </div>
                                    <div class="col-sm-10">
                                        <div class="product-price">
                                            <strong id="chosen_price" class="h4 fw-600 text-primary">

                                            </strong>
                                        </div>
                                    </div>
                                </div>

                            </form>

                            @php
                            $tabby_min_amount = get_setting('TABBY_MIN_AMOUNT');
                            $installment_price = $final_price / 4;
                        @endphp
                            @if (get_setting('tabby_payment') == 1 )
                            <div class="card rounded border-0 shadow-sm mb-4 mt-3" id="tabby-payment-options">
                                <div class="card-body p-0">
                                    <div class="p-3">
                                        <div class="card rounded border-0 shadow-sm mb-4 mt-3 d-none" id="tabby-payment-box">
                                            <div class="card-body p-0">
                                                <div class="p-3">

                                                    <div id="tabby-max-amount-box" class="tabby-promo-snippet mb-4 mt-3 d-none" onclick="showTabbyDetails()" style="cursor: pointer; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; background: #f9fafb;">
                                                        <div class="tabby-promo-content" style="display: flex; align-items: center; justify-content: space-between;">
                                                            <div class="tabby-promo-text text-sm text-gray-700 flex items-center space-x-2">
                                                                <span>{{ translate('4 interest-free installments starting from') }}</span>
                                                                <strong class="text-lg text-gray-900" id="tabby-price-value">--</strong>
                                                                <span class="text-lg text-gray-900">/</span>
                                                                <strong class="text-lg text-gray-900">{{ translate('month') }}</strong>
                                                                <span>{{ translate('Sharia compliant.') }}</span>
                                                                <span>{{ translate('Learn more.') }}</span>
                                                            </div>
                                                            <img src="{{ static_asset('assets/img/cards/tabby_payment.png') }}" style="height: 24px" alt="Tabby">
                                                        </div>
                                                    </div>

                                                    <div id="tabby-min-amount-box" class="tabby-promo-snippet mb-4 mt-3 d-none" style=" border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; background: #f9fafb;">
                                                        <div class="tabby-promo-content" style="display: flex; align-items: center; justify-content: space-between;">
                                                            <div class="tabby-promo-text text-sm text-gray-700 flex items-center space-x-2">
                                                                <span class="text-lg text-gray-900">
                                                                    {{ translate('Tabby is available for orders over') }} {{ single_price($tabby_min_amount) }}.
                                                                    {{ translate('Add more items to your cart to use Tabby at checkout.') }}
                                                                </span>
                                                            </div>
                                                            <img src="{{ static_asset('assets/img/cards/tabby_payment.png') }}" style="height: 24px" alt="Tabby">
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabby Details Modal -->
                            <div class="modal fade" id="tabbyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="tabbyDetailsModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
                                    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);">
                                        <div class="modal-header" style="border-bottom: none; padding: 24px 24px 0;">
                                            <div class="modal-close" style="position: absolute; left: 24px; top: 24px; cursor: pointer; z-index: 1;" data-dismiss="modal">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M18 6L6 18" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6 6L18 18" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </div>
                                            <div style="width: 100%; text-align: center; padding-top: 8px;">
                                                <img src="{{ static_asset('assets/img/cards/tabby_payment.png') }}" style="height: 48px; margin-bottom: 16px;" alt="Tabby">
                                            </div>
                                        </div>
                                        <div class="modal-body" style="padding: 0 24px 24px;">
                                            <h3 style="font-size: 20px; font-weight: 700; text-align: center; margin-bottom: 8px; color: #111827; line-height: 1.3;">
                                                {{ translate('Divide it into 4 interest-free installments') }}
                                            </h3>
                                            <p style="text-align: center; color: #6B7280; margin-bottom: 24px; font-size: 15px; line-height: 1.5;">
                                                {{ translate('Split your purchases and pay at your convenience') }}
                                            </p>

                                            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 24px; border: 1px solid #e2e8f0;">
                                                <p style="font-size: 18px; font-weight: 600; margin-bottom: 6px; color: #111827; display: flex; align-items: center; justify-content: center;">
                                                    4 {{translate('installments of')}} <span id="tabby-price-in-modal">--</span>
                                                    <svg viewBox="0 0 11 13" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin: 0 4px;">
                                                        <path d="M10.21 7.76a3.815 3.815 0 0 1-.321 1.194l-3.554.75v-2.25l-1.107.234v1.249c0 .114-.035.22-.095.307l-.576.849a.998.998 0 0 1-.618.41L.8 11.166c.043-.421.154-.823.321-1.193l3-.634V7.922l-2.799.592c.043-.422.154-.823.322-1.193l2.477-.524V2.422A3.899 3.899 0 0 1 5.228 1.5v5.064l1.107-.234V2.973a3.9 3.9 0 0 1 1.107-.924v4.046l2.768-.584a3.81 3.81 0 0 1-.321 1.193l-2.447.517v1.125l2.768-.585ZM6.335 11.954c.043-.42.154-.822.322-1.193l3.553-.75a3.814 3.814 0 0 1-.321 1.192l-3.554.751Z" fill="currentColor"></path>
                                                    </svg>
                                                    /{{translate('month')}}
                                                </p>
                                                <p style="color: #10B981; font-size: 14px; font-weight: 600; margin: 0;">
                                                    {{ translate('No fees or interest. Sharia-compliant') }}
                                                </p>
                                            </div>

                                            <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #111827; position: relative; padding-bottom: 12px;">
                                                <span style="content: ''; position: absolute; bottom: 0; left: 0; width: 40px; height: 3px; background: #0063B2FF; border-radius: 3px;"></span>
                                                {{ translate('How Tabby works') }}
                                            </h4>

                                            <div style="margin-bottom: 24px;">
                                                <div style="display: flex; margin-bottom: 20px; align-items: flex-start;">
                                                    <div style="background: #0063B2FF; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-left: 12px; flex-shrink: 0; box-shadow: 0 4px 6px rgba(0, 99, 178, 0.2);">
                                                        1
                                                    </div>
                                                    <div>
                                                        <p style="color: #111827; margin: 0 0 4px 0; font-size: 15px; font-weight: 600; margin-left: 7px;">
                                                            {{ translate('Select a payment plan') }}
                                                        </p>
                                                        <p style="color: #64748B; margin: 0; font-size: 14px; line-height: 1.5;">
                                                            {{ translate('Choose Tabby at checkout to defer your payment') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div style="display: flex; margin-bottom: 20px; align-items: flex-start;">
                                                    <div style="background: #0063B2FF; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-left: 12px; flex-shrink: 0; box-shadow: 0 4px 6px rgba(0, 99, 178, 0.2);">
                                                        2
                                                    </div>
                                                    <div>
                                                        <p style="color: #111827; margin: 0 0 4px 0; font-size: 15px; font-weight: 600; margin-left: 7px;">
                                                            {{ translate('Enter your details') }}
                                                        </p>
                                                        <p style="color: #64748B; margin: 0; font-size: 14px; line-height: 1.5;">
                                                            {{ translate('Add your debit or credit card information securely') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div style="display: flex; margin-bottom: 20px; align-items: flex-start;">
                                                    <div style="background: #0063B2FF; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-left: 12px; flex-shrink: 0; box-shadow: 0 4px 6px rgba(0, 99, 178, 0.2);">
                                                        3
                                                    </div>
                                                    <div>
                                                        <p style="color: #111827; margin: 0 0 4px 0; font-size: 15px; font-weight: 600; margin-left: 7px;">
                                                            {{ translate('First payment processed') }}
                                                        </p>
                                                        <p style="color: #64748B; margin: 0; font-size: 14px; line-height: 1.5; ">
                                                            {{ translate('Your first installment will be charged when order is completed') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div style="display: flex; align-items: flex-start;">
                                                    <div style="background: #0063B2FF; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-left: 12px; flex-shrink: 0; box-shadow: 0 4px 6px rgba(0, 99, 178, 0.2);">
                                                        4
                                                    </div>
                                                    <div>
                                                        <p style="color: #111827; margin: 0 0 4px 0; font-size: 15px; font-weight: 600; margin-left: 7px;">
                                                            {{ translate('Payment reminders') }}
                                                        </p>
                                                        <p style="color: #64748B; margin: 0; font-size: 14px; line-height: 1.5;">
                                                            {{ translate('We will notify you when your next payment is due') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 24px; border: 1px solid #e2e8f0;">
                                                <div style="display: flex; align-items: center; margin-bottom: 16px;">
                                                    <div style="background: #EFF6FF; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 12px; flex-shrink: 0;">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M18.235 5.8 17 2l-1.235 3.8H11.77l3.233 2.35-1.235 3.8L17 9.6l3.233 2.35-1.235-3.8 3.233-2.35h-3.996ZM17.5 16a8.959 8.959 0 0 0 4.625-1.278 9.951 9.951 0 0 1-1.255 2.752A9.991 9.991 0 0 1 12.5 22c-5.523 0-10-4.477-10-10a9.991 9.991 0 0 1 4.815-8.552 9.945 9.945 0 0 1 2.463-1.073A9 9 0 0 0 17.5 16ZM6.504 6.704C6.5 6.802 6.5 6.9 6.5 7c0 6.075 4.925 11 11 11 .099 0 .198-.001.296-.004A8 8 0 0 1 6.504 6.704Z" fill="#0063B2FF"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p style="color: #111827; margin: 0; font-size: 15px; font-weight: 600;">
                                                            {{ translate('Sharia compliant') }}
                                                        </p>
                                                        <p style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
                                                            {{ translate('Fully compliant with Islamic finance principles') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div style="display: flex; align-items: center; margin-bottom: 16px;">
                                                    <div style="background: #EFF6FF; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 12px; flex-shrink: 0;">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.5 10a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm0 2a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 16a5 5 0 0 0-5 5v1H1v-1a7 7 0 0 1 7-7h3a7 7 0 0 1 7 7v1h-2v-1a5 5 0 0 0-5-5H8Zm15 4v2h-2v-2a4 4 0 0 0-2.242-3.594c-.428-.21-.758-.613-.758-1.09 0-.645.586-1.14 1.187-.905A6.002 6.002 0 0 1 23 20Zm-6.917-8.287c-.557.21-1.083-.261-1.083-.857v-.089c0-.475.35-.867.76-1.107a2.499 2.499 0 0 0 0-4.32c-.41-.24-.76-.632-.76-1.107v-.089c0-.596.526-1.067 1.083-.858a4.502 4.502 0 0 1 0 8.427Z" fill="#0063B2FF"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p style="color: #111827; margin: 0; font-size: 15px; font-weight: 600;">
                                                            {{ translate('Trusted by millions') }}
                                                        </p>
                                                        <p style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
                                                            {{ translate('Over 5 million customers across the region') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div style="display: flex; align-items: center;">
                                                    <div style="background: #EFF6FF; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 12px; flex-shrink: 0;">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M22.5 12c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10 10 4.477 10 10Zm-2 0a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.082-4.332-7.25 7.25 1.414 1.414 7.25-7.25-1.414-1.414Zm-.168 8.582a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm-4-7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" fill="#0063B2FF"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p style="color: #111827; margin: 0; font-size: 15px; font-weight: 600;">
                                                            {{ translate('No hidden fees') }}
                                                        </p>
                                                        <p style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
                                                            {{ translate('No interest or late payment fees') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="display: flex; align-items: center; background: #F1F5F9; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                                                <div style="background: #0063B2FF; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 12px; flex-shrink: 0;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="m7.97 15.764 2.527 2.527 9.741-9.742a3.574 3.574 0 0 0-5.053 0L7.97 15.764ZM6.288 9.056l-2.527 2.527 3.367 3.367a3.574 3.574 0 0 0 0-5.054l-.84-.84Z" fill="#fff"></path>
                                                    </svg>
                                                </div>
                                                <div style="flex: 1;">
                                                    <p style="font-size: 15px; font-weight: 700; margin-bottom: 4px; color: #111827; margin-left: 7px;">
                                                        {{ translate('Shop safely with Tabby') }}
                                                    </p>
                                                    <p style="font-size: 13px; color: #64748B; margin: 0; line-height: 1.4; margin-left: 7px;">
                                                        {{ translate('Your purchases are protected with our Buyer Protection program') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <button type="button" style="width: 100%; background: #0063B2FF; color: white; border: none; border-radius: 8px; padding: 14px; font-weight: 600; cursor: pointer; font-size: 15px; transition: all 0.2s;"
                                                    onmouseover="this.style.backgroundColor='#0056a3'"
                                                    onmouseout="this.style.backgroundColor='#0063B2FF'"
                                                    onclick="$('#tabbyDetailsModal').modal('hide')">
                                                {{ translate('Continue Shopping') }}
                                            </button>

                                            {{-- <div style="display: flex; justify-content: center; margin-top: 24px; gap: 12px;">
                                                <img src="{{ static_asset('assets/img/cards/visa.png') }}" style="height: 24px" alt="VISA">
                                                <img src="{{ static_asset('assets/img/cards/mastercard.png') }}" style="height: 24px" alt="Mastercard">
                                                <img src="{{ static_asset('assets/img/cards/mada.png') }}" style="height: 24px" alt="Mada">
                                                <img src="{{ static_asset('assets/img/cards/applepay.png') }}" style="height: 24px" alt="Apple Pay">
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <script>
                            function showTabbyDetails() {
                                    // Show modal
                                    $('#tabbyDetailsModal').modal('show');
                                    var price = document.getElementById('tabby-price-value')?.innerText || '--';
                                    if (price === '--') {
                                        price = "{{ single_price($final_price / 4) }}";
                                    }
                                    document.getElementById('tabby-price-in-modal').innerText = price;
                                }
                            </script>
                            @else
                            <div class="card rounded border-0 shadow-sm mb-4 mt-3">
                                <div class="card-body p-3">
                                    <div class="p-3">
                                        <div class="tabby-promo-snippet mb-4 mt-3" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; background: #f9fafb;">
                                            <div class="tabby-promo-content text-gray-700">
                                                <div class="tabby-promo-text text-sm text-gray-700 flex items-center space-x-2">
                                                <span class="text-lg text-gray-900">
                                                    {{ translate('Tabby is available for orders over') }} {{ single_price(get_setting('TABBY_MIN_AMOUNT')) }}.
                                                    {{ translate('Add more items to your cart to use Tabby at checkout.') }}
                                                </span>
                                                </div>
                                            </div>
                                            <img src="{{ static_asset('assets/img/cards/tabby_payment.png') }}" style="height: 24px" alt="Tabby">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                            <div class="mt-3">
                                @if ($detailedProduct->external_link != null)
                                    <a type="button" class="btn btn-primary buy-now fw-600"
                                        href="{{ $detailedProduct->external_link }}">
                                        <i class="la la-share"></i> {{ translate($detailedProduct->external_link_btn) }}
                                    </a>
                                @else
                                    <button type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600"
                                        onclick="addToCart()">
                                        <i class="las la-shopping-bag"></i>
                                        <span class="d-none d-md-inline-block"> {{ translate('Add to cart') }}</span>
                                    </button>
                                    <button type="button" class="btn btn-primary buy-now fw-600" onclick="buyNow()">
                                        <i class="la la-shopping-cart"></i> {{ translate('Buy Now') }}
                                    </button>
                                    @if(get_setting('order_with_whatsapp_enabled') == 'true' && get_setting('order_whatsapp_phone') != '')
                                        <button type="button" class="btn btn-success fw-600" id="order-via-whatsapp">
                                            <i class="la la-whatsapp"></i> {{ translate('Order Via Whatsapp') }}
                                        </button>
                                    @endif
                                @endif
                                <button type="button" class="btn btn-secondary out-of-stock fw-600 d-none" disabled>
                                    <i class="la la-cart-arrow-down"></i> {{ translate('Out of Stock') }}
                                </button>
                            </div>



                            <div class="d-table width-100 mt-3">
                                <div class="d-table-cell">
                                    <!-- Add to wishlist button -->
                                    <button type="button" class="btn pl-0 btn-link fw-600"
                                        onclick="addToWishList({{ $detailedProduct->id }})">
                                        {{ translate('Add to wishlist') }}
                                    </button>
                                    <!-- Add to compare button -->
                                    <button type="button" class="btn btn-link btn-icon-left fw-600"
                                        onclick="addToCompare({{ $detailedProduct->id }})">
                                        {{ translate('Add to compare') }}
                                    </button>
                                    @if (Auth::check() && addon_is_activated('affiliate_system') && (\App\Models\AffiliateOption::where('type', 'product_sharing')->first()->status || \App\Models\AffiliateOption::where('type', 'category_wise_affiliate')->first()->status) && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                                        @php
                                            if (Auth::check()) {
                                                if (Auth::user()->referral_code == null) {
                                                    Auth::user()->referral_code = substr(Auth::user()->id . Str::random(10), 0, 10);
                                                    Auth::user()->save();
                                                }
                                                $referral_code = Auth::user()->referral_code;
                                                $referral_code_url = URL::to('/product') . '/' . $detailedProduct->slug . "?product_referral_code=$referral_code";
                                            }
                                        @endphp
                                        <div>
                                            <button type=button id="ref-cpurl-btn" class="btn btn-sm btn-secondary"
                                                data-attrcpy="{{ translate('Copied') }}"
                                                onclick="CopyToClipboard(this)"
                                                data-url="{{ $referral_code_url }}">{{ translate('Copy the Promote Link') }}</button>
                                        </div>
                                    @endif
                                </div>
                            </div>


                            @php
                                $refund_sticker = get_setting('refund_sticker');
                            @endphp
                            @if (addon_is_activated('refund_request'))
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ translate('Refund') }}:</div>
                                    </div>
                                    <div class="col-10">
                                        <a href="{{ route('returnpolicy') }}" target="_blank">
                                            @if ($refund_sticker != null)
                                                <img src="{{ uploaded_asset($refund_sticker) }}" height="36">
                                            @else
                                                <img src="{{ asset('public/assets/img/refund-sticker.jpg') }}"
                                                    height="36">
                                            @endif
                                        </a>
                                        <a href="{{ route('returnpolicy') }}" class="ml-2"
                                            target="_blank">{{ translate('View Policy') }}</a>
                                    </div>
                                </div>
                            @endif
                            <div class="row no-gutters mt-4">
                                <div class="col-sm-2">
                                    <div class="opacity-50 my-2">{{ translate('Share') }}:</div>
                                </div>
                                <div class="col-sm-10">
                                    <div class="aiz-share"></div>
                                </div>
                            </div>
                                                        <!--Ahmed Mousa-->
                            <div class="bg-white mb-3 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab"
                                class="p-3 fs-16 fw-600 text-reset active show">{{ translate('Description') }}</a>
                            @if ($detailedProduct->video_link != null)
                                <a href="#tab_default_2" data-toggle="tab"
                                    class="p-3 fs-16 fw-600 text-reset">{{ translate('Video') }}</a>
                            @endif
                            @if ($detailedProduct->pdf != null)
                                <a href="#tab_default_3" data-toggle="tab"
                                    class="p-3 fs-16 fw-600 text-reset">{{ translate('Downloads') }}</a>
                            @endif
                            <a href="#tab_default_4" data-toggle="tab"
                                class="p-3 fs-16 fw-600 text-reset">{{ translate('Reviews') }}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-auto text-left aiz-editor-data">
                                        <?php echo $detailedProduct->getTranslation('description'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab_default_2">
                                <div class="p-4">
                                    <div class="embed-responsive embed-responsive-16by9">
                                        @if ($detailedProduct->video_provider == 'youtube' && isset(explode('=', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item"
                                                src="https://www.youtube.com/embed/{{ get_url_params($detailedProduct->video_link, 'v') }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'dailymotion' && isset(explode('video/', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item"
                                                src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'vimeo' && isset(explode('vimeo.com/', $detailedProduct->video_link)[1]))
                                            <iframe
                                                src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}"
                                                width="500" height="281" frameborder="0" webkitallowfullscreen
                                                mozallowfullscreen allowfullscreen></iframe>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_default_3">
                                <div class="p-4 text-center ">
                                    <a href="{{ uploaded_asset($detailedProduct->pdf) }}"
                                        class="btn btn-primary">{{ translate('Download') }}</a>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_default_4">
                                <div class="p-4">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($detailedProduct->reviews as $key => $review)
                                            @if ($review->user != null)
                                                <li class="media list-group-item d-flex">
                                                    <span class="avatar avatar-md mr-3">
                                                        <img class="lazyload"
                                                            src="{{ asset('public/assets/img/placeholder.jpg') }}"
                                                            onerror="this.onerror=null;this.src='{{ asset('public/assets/img/placeholder.jpg') }}';"
                                                            @if ($review->user->avatar_original != null) data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                        @else
                                                            data-src="{{ asset('public/assets/img/placeholder.jpg') }}" @endif>
                                                    </span>
                                                    <div class="media-body text-left">
                                                        <div class="d-flex justify-content-between">
                                                            <h3 class="fs-15 fw-600 mb-0">{{ $review->user->name }}
                                                            </h3>
                                                            <span class="rating rating-sm">
                                                                @for ($i = 0; $i < $review->rating; $i++)
                                                                    <i class="las la-star active"></i>
                                                                @endfor
                                                                @for ($i = 0; $i < 5 - $review->rating; $i++)
                                                                    <i class="las la-star"></i>
                                                                @endfor
                                                            </span>
                                                        </div>
                                                        <div class="opacity-60 mb-2">
                                                            {{ date('d-m-Y', strtotime($review->created_at)) }}</div>
                                                        <p class="comment-text">
                                                            {{ $review->comment }}
                                                        </p>
                                                    </div>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    @if (count($detailedProduct->reviews) <= 0)
                                        <div class="text-center fs-18 opacity-70">
                                            {{ translate('There have been no reviews for this product yet.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                <div class="col-xl-3 order-1 order-xl-0">
                    @if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->shop != null)
                        <div class="bg-white shadow-sm mb-3">
                            <div class="position-relative p-3 text-left">
                                @if ($detailedProduct->user->shop->verification_status)
                                    <div class="absolute-top-right p-2 bg-white z-1">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve"
                                            viewBox="0 0 287.5 442.2" width="22" height="34">
                                            <polygon style="fill:#F8B517;"
                                                points="223.4,442.2 143.8,376.7 64.1,442.2 64.1,215.3 223.4,215.3 " />
                                            <circle style="fill:#FBD303;" cx="143.8" cy="143.8"
                                                r="143.8" />
                                            <circle style="fill:#F8B517;" cx="143.8" cy="143.8"
                                                r="93.6" />
                                            <polygon style="fill:#FCFCFD;"
                                                points="143.8,55.9 163.4,116.6 227.5,116.6 175.6,154.3 195.6,215.3 143.8,177.7 91.9,215.3 111.9,154.3
                                            60,116.6 124.1,116.6 " />
                                        </svg>
                                    </div>
                                @endif
                                <div class="opacity-50 fs-12 border-bottom">{{ translate('Sold by') }}</div>
                                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}"
                                    class="text-reset d-block fw-600">
                                    {{ $detailedProduct->user->shop->name }}
                                    @if ($detailedProduct->user->shop->verification_status == 1)
                                        <span class="ml-2"><i class="fa fa-check-circle"
                                                style="color:green"></i></span>
                                    @else
                                        <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                    @endif
                                </a>
                                <div class="location opacity-70">{{ $detailedProduct->user->shop->address }}</div>
                                <div class="text-center border rounded p-2 mt-3">
                                    <div class="rating">
                                        @if ($total > 0)
                                            {{ renderStarRating($detailedProduct->user->shop->rating) }}
                                        @else
                                            {{ renderStarRating(0) }}
                                        @endif
                                    </div>
                                    <div class="opacity-60 fs-12">({{ $total }}
                                        {{ translate('customer reviews') }})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}"
                                        class="d-block btn btn-soft-primary rounded-0">{{ translate('Visit Store') }}</a>
                                </div>
                                <div class="col">
                                    <ul class="social list-inline mb-0">
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->facebook }}" class="facebook"
                                                target="_blank">
                                                <i class="lab la-facebook-f opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->google }}" class="google"
                                                target="_blank">
                                                <i class="lab la-google opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->twitter }}" class="twitter"
                                                target="_blank">
                                                <i class="lab la-twitter opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="{{ $detailedProduct->user->shop->youtube }}" class="youtube"
                                                target="_blank">
                                                <i class="lab la-youtube opacity-60"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="bg-white rounded shadow-sm mb-3">
                        <div class="p-3 border-bottom fs-16 fw-600">
                            {{ translate('Top Selling Products') }}
                        </div>
                        <div class="p-3">
                            <ul class="list-group list-group-flush">
                                @foreach (filter_products(\App\Models\Product::where('user_id', $detailedProduct->user_id)->orderBy('num_of_sale', 'desc'))->limit(6)->get()
        as $key => $top_product)
                                    <li class="py-3 px-0 list-group-item border-light">
                                        <div class="row gutters-10 align-items-center">
                                            <div class="col-5">
                                                <a href="{{ route('product', $top_product->slug) }}"
                                                    class="d-block text-reset">
                                                    <img class="img-fit lazyload h-xxl-110px h-xl-80px h-120px"
                                                        src="{{ asset('public/assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($top_product->thumbnail_img) }}"
                                                        alt="{{ $top_product->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ asset('public/assets/img/placeholder.jpg') }}';">
                                                </a>
                                            </div>
                                            <div class="col-7 text-left">
                                                <h4 class="fs-13 text-truncate-2">
                                                    <a href="{{ route('product', $top_product->slug) }}"
                                                        class="d-block text-reset">{{ $top_product->getTranslation('name') }}</a>
                                                </h4>
                                                <div class="rating rating-sm mt-1">
                                                    {{ renderStarRating($top_product->rating) }}
                                                </div>
                                                <div class="mt-2">
                                                    <span
                                                        class="fs-17 fw-600 text-primary">{{ home_discounted_base_price($top_product) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 order-0 order-xl-1">



                    <div class="bg-white rounded shadow-sm">
                        <div class="border-bottom p-3">
                            <h3 class="fs-16 fw-600 mb-0">
                                <span class="mr-4">{{ translate('Related products') }}</span>
                            </h3>
                        </div>
                        <div class="p-3">
                            <div class="aiz-carousel gutters-5 half-outside-arrow" data-items="5" data-xl-items="3"
                                data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2"
                                data-arrows='true' data-infinite='true'>
                                @foreach (filter_products(\App\Models\Product::where('category_id', $detailedProduct->category_id)->where('id', '!=', $detailedProduct->id))->limit(10)->get() as $key => $related_product)
                                    <div class="carousel-box">
                                        <div
                                            class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                            <div class="">
                                                <a href="{{ route('product', $related_product->slug) }}"
                                                    class="d-block">
                                                    <img class="img-fit lazyload mx-auto h-140px h-md-210px"
                                                        src="{{ asset('public/assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($related_product->thumbnail_img) }}"
                                                        alt="{{ $related_product->getTranslation('name') }}"
                                                        onerror="this.onerror=null;this.src='{{ asset('public/assets/img/placeholder.jpg') }}';">
                                                </a>
                                            </div>
                                            <div class="p-md-3 p-2 text-left">
                                                <div class="fs-15">
                                                    @if (home_base_price($related_product) != home_discounted_base_price($related_product))
                                                        <del
                                                            class="fw-600 opacity-50 mr-1">{{ home_base_price($related_product) }}</del>
                                                    @endif
                                                    <span
                                                        class="fw-700 text-primary">{{ home_discounted_base_price($related_product) }}</span>
                                                </div>
                                                <div class="rating rating-sm mt-1">
                                                    {{ renderStarRating($related_product->rating) }}
                                                </div>
                                                <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
                                                    <a href="{{ route('product', $related_product->slug) }}"
                                                        class="d-block text-reset">{{ $related_product->getTranslation('name') }}</a>
                                                </h3>
                                                @if (addon_is_activated('club_point'))
                                                    <div
                                                        class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                                        {{ translate('Club Point') }}:
                                                        <span
                                                            class="fw-700 float-right">{{ $related_product->earn_point }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    {{-- Product Query --}}
                    @if(get_setting('product_query_activation') == 1)
                        <div class="bg-white rounded shadow-sm mt-3">
                            <div class="border-bottom p-3">
                                <h3 class="fs-18 fw-600 mb-0">
                                    <span>{{ translate(' Product Queries ') }} ({{ $total_query }})</span>
                                </h3>
                            </div>
                            @guest
                                <p class="fs-14 fw-400 mb-0 ml-3 mt-2"><a
                                        href="{{ route('user.login') }}">{{ translate('Login') }}</a> or <a class="mr-1"
                                        href="{{ route('user.registration') }}">{{ translate('Register ') }}</a>{{ translate(' to submit your questions to seller') }}
                                </p>
                            @endguest
                            @auth
                                <div class="query form p-3">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form action="{{ route('product-queries.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product" value="{{ $detailedProduct->id }}">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="3" cols="40" name="question"
                                                placeholder="{{ translate('Write your question here...') }}" style="resize: none;"></textarea>

                                        </div>
                                        <button type="submit" class="btn btn-primary">{{ translate('Submit') }}</button>
                                    </form>
                                </div>
                                @php
                                    $own_product_queries = Auth::user()->product_queries->where('product_id',$detailedProduct->id);
                                @endphp
                                @if ($own_product_queries->count() > 0)

                                    <div class="question-area my-4   mb-0 ml-3">

                                        <div class="border-bottom py-3">
                                            <h3 class="fs-18 fw-600 mb-0">
                                                <span class="mr-4">{{ translate('My Questions') }}</span>
                                            </h3>
                                        </div>
                                        @foreach ($own_product_queries as $product_query)
                                            <div class="produc-queries border-bottom">
                                                <div class="query d-flex my-4">
                                                    <span class="mt-1"><svg xmlns="http://www.w3.org/2000/svg" width="24.994"
                                                            height="24.981" viewBox="0 0 24.994 24.981">
                                                            <g id="Group_23909" data-name="Group 23909"
                                                                transform="translate(18392.496 11044.037)">
                                                                <path id="Subtraction_90" data-name="Subtraction 90"
                                                                    d="M1830.569-117.742a.4.4,0,0,1-.158-.035.423.423,0,0,1-.252-.446c0-.84,0-1.692,0-2.516v-2.2a5.481,5.481,0,0,1-2.391-.745,5.331,5.331,0,0,1-2.749-4.711c-.034-2.365-.018-4.769,0-7.094l0-.649a5.539,5.539,0,0,1,4.694-5.513,5.842,5.842,0,0,1,.921-.065q3.865,0,7.73,0l5.035,0a5.539,5.539,0,0,1,5.591,5.57c.01,2.577.01,5.166,0,7.693a5.54,5.54,0,0,1-4.842,5.506,6.5,6.5,0,0,1-.823.046l-3.225,0c-1.454,0-2.753,0-3.97,0a.555.555,0,0,0-.435.182c-1.205,1.214-2.435,2.445-3.623,3.636l-.062.062-1.005,1.007-.037.037-.069.069A.464.464,0,0,1,1830.569-117.742Zm7.37-11.235h0l1.914,1.521.817-.754-1.621-1.273a3.517,3.517,0,0,0,1.172-1.487,5.633,5.633,0,0,0,.418-2.267v-.58a5.629,5.629,0,0,0-.448-2.323,3.443,3.443,0,0,0-1.282-1.525,3.538,3.538,0,0,0-1.93-.53,3.473,3.473,0,0,0-1.905.534,3.482,3.482,0,0,0-1.288,1.537,5.582,5.582,0,0,0-.454,2.314v.654a5.405,5.405,0,0,0,.471,2.261,3.492,3.492,0,0,0,1.287,1.5,3.492,3.492,0,0,0,1.9.527,3.911,3.911,0,0,0,.947-.112Zm-.948-.9a2.122,2.122,0,0,1-1.812-.9,4.125,4.125,0,0,1-.652-2.457v-.667a4.008,4.008,0,0,1,.671-2.4,2.118,2.118,0,0,1,1.78-.863,2.138,2.138,0,0,1,1.824.869,4.145,4.145,0,0,1,.639,2.473v.673a4.07,4.07,0,0,1-.655,2.423A2.125,2.125,0,0,1,1836.991-129.881Z"
                                                                    transform="translate(-20217 -10901.814)" fill="#e62e04"
                                                                    stroke="rgba(0,0,0,0)" stroke-miterlimit="10"
                                                                    stroke-width="1" />
                                                            </g>
                                                        </svg></span>

                                                    <div class="ml-3">
                                                        <div class="fs-14">{{ strip_tags($product_query->question) }}</div>
                                                        <span class="text-secondary">{{ $product_query->user->name }} </span>
                                                    </div>
                                                </div>
                                                <div class="answer d-flex my-4">
                                                    <span class="mt-1"> <svg xmlns="http://www.w3.org/2000/svg" width="24.99"
                                                            height="24.98" viewBox="0 0 24.99 24.98">
                                                            <g id="Group_23908" data-name="Group 23908"
                                                                transform="translate(17952.169 11072.5)">
                                                                <path id="Subtraction_89" data-name="Subtraction 89"
                                                                    d="M2162.9-146.2a.4.4,0,0,1-.159-.035.423.423,0,0,1-.251-.446q0-.979,0-1.958V-151.4a5.478,5.478,0,0,1-2.39-.744,5.335,5.335,0,0,1-2.75-4.712c-.034-2.355-.018-4.75,0-7.065l0-.678a5.54,5.54,0,0,1,4.7-5.513,5.639,5.639,0,0,1,.92-.064c2.527,0,5.029,0,7.437,0l5.329,0a5.538,5.538,0,0,1,5.591,5.57c.01,2.708.01,5.224,0,7.692a5.539,5.539,0,0,1-4.843,5.506,6,6,0,0,1-.822.046l-3.234,0c-1.358,0-2.691,0-3.96,0a.556.556,0,0,0-.436.182c-1.173,1.182-2.357,2.367-3.5,3.514l-1.189,1.192-.047.048-.058.059A.462.462,0,0,1,2162.9-146.2Zm5.115-12.835h3.559l.812,2.223h1.149l-3.25-8.494h-.98l-3.244,8.494h1.155l.8-2.222Zm3.226-.915h-2.888l1.441-3.974,1.447,3.972Z"
                                                                    transform="translate(-20109 -10901.815)" fill="#f7941d"
                                                                    stroke="rgba(0,0,0,0)" stroke-miterlimit="10"
                                                                    stroke-width="1" />
                                                            </g>
                                                        </svg></span>

                                                    <div class="ml-3">
                                                        <div class="fs-14">
                                                            {{ strip_tags($product_query->reply ? $product_query->reply : translate('Seller did not respond yet')) }}
                                                        </div>
                                                        <span class=" text-secondary">
                                                            {{ $product_query->product->user->name }} </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                @endif
                            @endauth

                            <div class="pagination-area my-4 mb-0 ml-3">
                                @include('frontend.partials.product_query_pagination')
                            </div>
                        </div>
                    @endif
                    {{-- End of Product Query --}}
                </div>
            </div>
        </div>
    </section>

@endsection

@section('modal')
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ translate('Any query about this product') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title"
                                value="{{ $detailedProduct->name }}" placeholder="{{ translate('Product Name') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required
                                placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ translate('Login') }}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}"
                            method="POST">
                            @csrf
                            <div class="form-group">
                                @if (addon_is_activated('otp_system'))
                                    <input type="text"
                                        class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone') }}"
                                        name="email" id="email">
                                @else
                                    <input type="email"
                                        class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}" placeholder="{{ translate('Email') }}"
                                        name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{ translate('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg"
                                    placeholder="{{ translate('Password') }}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{ translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}"
                                        class="text-reset opacity-60 fs-14">{{ translate('Forgot password?') }}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit"
                                    class="btn btn-primary btn-block fw-600">{{ translate('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ translate('Dont have an account?') }}</p>
                            <a href="{{ route('user.registration') }}">{{ translate('Register Now') }}</a>
                        </div>
                        @if (get_setting('google_login') == 1 || get_setting('facebook_login') == 1 || get_setting('twitter_login') == 1 || get_setting('apple_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ translate('Or Login With') }}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}"
                                            class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('google_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'google']) }}"
                                            class="google">
                                            <i class="lab la-google"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('twitter_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'twitter']) }}"
                                            class="twitter">
                                            <i class="lab la-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('apple_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'apple']) }}"
                                            class="apple">
                                            <i class="lab la-apple"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            console.log('Script section loaded'); // Debug: Confirm script is running
            getVariantPrice();

            // Bind click event to WhatsApp button
            $('#order-via-whatsapp').on('click', function() {
                console.log('WhatsApp button clicked'); // Debug: Confirm click
                buyNowWithWhatsapp();
            });

            // Quantity handling
            var total = parseFloat(@json($final_price ?? 0));

            function handleQuantityChange(context, isIncrease) {
                var input = context.find("input");
                var span = context.find("span[id^='quant_addon_']");
                var priceElement = context.find("span[id^='price_addon_']");
                var productAddonId = priceElement.attr('data-addon-id');
                var addonQuantityInput = $('#product_addon_id_' + productAddonId);

                var currentValue = parseInt(addonQuantityInput.val());
                var oldQuantity = currentValue;

                console.log("Before Update:", oldQuantity, isIncrease, currentValue, productAddonId, addonQuantityInput.val());

                if (isIncrease) {
                    currentValue += 1;
                } else if (currentValue > 0) {
                    currentValue -= 1;
                }

                input.val(currentValue);
                addonQuantityInput.val(currentValue);
                span.text(currentValue);

                var addonPrice = parseFloat(priceElement.attr('data-price'));
                var priceDifference = (currentValue - oldQuantity) * addonPrice;
                total += priceDifference;

                var currencySymbol = 'KD‎ ';
                getVariantPrice();
                $('#option-choice-form #chosen_price_div #chosen_price').text(currencySymbol + total.toFixed(3));

                console.log("After Update:", input.val(), span.text(), addonQuantityInput.val(), "Total:", total);
            }

            $(document).off("click.qtyChange", ".increase, .decrease");
            $(document).on("click.qtyChange", ".increase, .decrease", function(event) {
                event.preventDefault();
                var button = $(this);
                var context = button.closest(".quantity-input");

                if (button.hasClass("increase")) {
                    console.log('Increase Clicked');
                    handleQuantityChange(context, true);
                } else if (button.hasClass("decrease")) {
                    console.log('Decrease Clicked');
                    handleQuantityChange(context, false);
                }
            });

            // Pagination using ajax
            $(window).on('hashchange', function() {
                if (window.location.hash) {
                    var page = window.location.hash.replace('#', '');
                    if (page == Number.NaN || page <= 0) {
                        return false;
                    } else {
                        getQuestions(page);
                    }
                }
            });

            $(document).on('click', '.pagination a', function(e) {
                getQuestions($(this).attr('href').split('page=')[1]);
                e.preventDefault();
            });
        });

        function CopyToClipboard(e) {
            var url = $(e).data('url');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(url).select();
            try {
                document.execCommand("copy");
                AIZ.plugins.notify('success', '{{ translate('Link copied to clipboard') }}');
            } catch (err) {
                AIZ.plugins.notify('danger', '{{ translate('Oops, unable to copy') }}');
            }
            $temp.remove();
        }

        function show_chat_modal() {
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function getQuestions(page) {
            $.ajax({
                url: '?page=' + page,
                dataType: 'json',
            }).done(function(data) {
                $('.pagination-area').html(data);
                location.hash = page;
            }).fail(function() {
                alert('Something went wrong! Questions could not be loaded.');
            });
        }

        function buyNowWithWhatsapp() {
            console.log('buyNowWithWhatsapp called'); // Debug: Confirm function execution
            // Get selected options from the form
            var form = document.getElementById('option-choice-form');
            var formData = new FormData(form);
            
            // Validate all product options
            var missingSelections = [];
            
            // Check choice attributes (both dropdowns and radio buttons)
            document.querySelectorAll('[name^="attribute_id_"]').forEach(function(element) {
                var attributeName = element.closest('.row').querySelector('.opacity-50').textContent.trim();
                var isSelected = false;
                
                if(element.type === 'select-one') {
                    isSelected = element.value !== '';
                    if(!isSelected && !missingSelections.includes(attributeName)) {
                        missingSelections.push(attributeName);
                    }
                } else if(element.type === 'radio') {
                    var radioGroup = document.querySelectorAll(`[name="${element.name}"]`);
                    isSelected = Array.from(radioGroup).some(radio => radio.checked);
                    if(!isSelected && !missingSelections.includes(attributeName)) {
                        missingSelections.push(attributeName);
                    }
                }
            });

            // Check color selection if colors are available
            var colorInputs = document.querySelectorAll('input[name="color"]');
            if(colorInputs.length > 0) {
                if(!document.querySelector('input[name="color"]:checked')) {
                    missingSelections.push('Color');
                }
            }

            // Check add-ons if they are required
            document.querySelectorAll('[name^="product_addon_id_"]').forEach(function(addon) {
                var addonSection = addon.closest('.row');
                if(addonSection && addonSection.hasAttribute('required')) {
                    var addonName = addonSection.querySelector('.aiz-megabox-elem').textContent.trim();
                    if(addon.value === '0' || addon.value === '') {
                        missingSelections.push(addonName);
                    }
                }
            });

            // Show error if any selections are missing
            /*if(missingSelections.length > 0) {
                AIZ.plugins.notify('warning', 'Please select all product options: ' + missingSelections.join(', '));
                return;
            }*/
            
            // Get product details
            var productName = "{{ $detailedProduct->getTranslation('name') }}";
            var productPrice = document.querySelector('.text-primary').textContent.trim();
            var quantity = document.querySelector('[name="quantity"]').value;
            
            // Get selected attributes
            var selectedAttributes = [];
            document.querySelectorAll('[name^="attribute_id_"]').forEach(function(element) {
                if(element.type === 'radio' && !element.checked) return;
                var attributeName = element.closest('.row').querySelector('.opacity-50').textContent.trim();
                var attributeValue = element.type === 'select-one' ? 
                    element.options[element.selectedIndex].text : 
                    element.closest('.aiz-radio-inline').querySelector('input:checked + span').textContent.trim();
                if(attributeValue) {
                    selectedAttributes.push(attributeName + ': ' + attributeValue);
                }
            });
            
            // Get selected color if available
            var selectedColor = document.querySelector('input[name="color"]:checked');
            if(selectedColor) {
                selectedAttributes.push('Color: ' + selectedColor.value);
            }
            
            // Get selected addons if available
            var selectedAddons = [];
            document.querySelectorAll('[name^="product_addon_id_"]').forEach(function(addon) {
                if(addon.value > 0) {
                    var addonName = addon.closest('.aiz-megabox').querySelector('.aiz-megabox-elem').textContent.trim();
                    selectedAddons.push(addonName);
                }
            });
            
            // Construct message
            var message = "*New Order*\n";
            message += "Product: " + productName + "\n";
            message += "Price: " + productPrice + "\n";
            message += "Quantity: " + quantity + "\n";
            
            if(selectedAttributes.length > 0) {
                message += "\nSelected Options:\n" + selectedAttributes.join("\n");
            }
            
            if(selectedAddons.length > 0) {
                message += "\nSelected Addons:\n" + selectedAddons.join("\n");
            }
            
            // Encode message for URL
            var encodedMessage = encodeURIComponent(message);
            const phone = {{ get_setting('order_whatsapp_phone') }};
            
            // Open WhatsApp in new tab
            window.open(`https://api.whatsapp.com/send/?phone=${phone}&text=${encodedMessage}&type=phone_number&app_absent=0`, '_blank');
        }

        // Quantity buttons outside jQuery ready
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".quantity-input").forEach(function (container) {
                let decreaseBtn = container.querySelector(".decrease");
                let increaseBtn = container.querySelector(".increase");
                let quantitySpan = container.querySelector("span[id^='quant_addon_']");
                let quantityInput = container.querySelector("input[id^='quant_addon_']");

                function updateButtons() {
                    let quantity = parseInt(quantitySpan.innerText);
                    decreaseBtn.disabled = quantity === 0;
                }

                increaseBtn.addEventListener("click", function () {
                    let quantity = parseInt(quantitySpan.innerText);
                    quantity += 1;
                    quantitySpan.innerText = quantity;
                    quantityInput.value = quantity;
                    updateButtons();
                });

                decreaseBtn.addEventListener("click", function () {
                    var button = $(this);
                    var context = button.closest(".quantity-input");
                    console.log('Decrease Clicked');
                    handleQuantityChange(context, false);
                    let quantity = parseInt(quantitySpan.innerText);
                    if (quantity > 0) {
                        quantity -= 1;
                        quantitySpan.innerText = quantity;
                        quantityInput.value = quantity;
                    }
                    updateButtons();
                });

                updateButtons();
            });
        });
    </script>

    <script type="application/ld+json">
        {
          "@context": "https://schema.org/",
          "@type": "Product",
          "name": "{{ $detailedProduct->getTranslation('name') }}",
          "offers": {
            "@type": "Offer",
            "priceCurrency": "{{ \App\Models\Currency::findOrFail(\App\Models\BusinessSetting::where('type', 'system_default_currency')->first()->value)->code }}",
            "price":  "{{ $final_price }}",
          }
        }
    </script>
@endsection