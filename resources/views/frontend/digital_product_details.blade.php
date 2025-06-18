@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->getTranslation('meta_title')}}@stop

@section('meta_description'){{ $detailedProduct->getTranslation('meta_description') }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->getTranslation('meta_title')}}">
    <meta itemprop="description" content="{{ $detailedProduct->getTranslation('meta_description') }}">
    <meta itemprop="image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->getTranslation('meta_title')}}">
    <meta name="twitter:description" content="{{ $detailedProduct->getTranslation('meta_description') }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $detailedProduct->getTranslation('meta_title')}}" />
    <meta property="og:type" content="product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->getTranslation('meta_description') }}" />
    <meta property="og:site_name" content="{{ get_setting('meta_title') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
@endsection

@section('content')

    <section class="mb-4 pt-3">
        <div class="container">
            <div class="bg-white shadow-sm rounded p-3">
                <div class="row">
                    <div class="col-xl-5 col-lg-6">
                        <div class="sticky-top z-3 row gutters-10 flex-row-reverse">
                            @if($detailedProduct->photos != null)
                                @php
                                    $photos = explode(',',$detailedProduct->photos);
                                @endphp
                                <div class="col">
                                    <div class="aiz-carousel product-gallery" data-nav-for='.product-gallery-thumb' data-fade='true'>
                                        @foreach ($photos as $key => $photo)
                                        <div class="carousel-box img-zoom rounded">
                                            <img
                                                class="img-fluid lazyload"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-auto w-90px">
                                    <div class="aiz-carousel carousel-thumb product-gallery-thumb" data-items='5' data-nav-for='.product-gallery' data-vertical='true' data-focus-select='true'>
                                        @foreach ($photos as $key => $photo)
                                        <div class="carousel-box c-pointer border p-1 rounded">
                                            <img
                                                class="lazyload mw-100 size-60px mx-auto"
                                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ uploaded_asset($photo) }}"
                                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                            >
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                        </div>
                    </div>

                    <div class="col-xl-7 col-lg-6">
                        <div class="text-left">
                            <h1 class="mb-2 fs-20 fw-600">
                                {{ $detailedProduct->getTranslation('name') }}
                            </h1>

                            <div class="row align-items-center">
                                <div class="col-6">
                                    @php
                                        $total = 0;
                                        $total += $detailedProduct->reviews->count();
                                    @endphp
                                    <span class="rating">
                                        {{ renderStarRating($detailedProduct->rating) }}
                                    </span>
                                    <span class="ml-1 opacity-50">({{ $total }} {{ translate('reviews')}})</span>
                                </div>
                                <div class="col-6 text-right">
                                    @php
                                        $qty = 0;
                                        //if($detailedProduct->variant_product){
                                            foreach ($detailedProduct->stocks as $key => $stock) {
                                                $qty += $stock->qty;
                                            }
                                        //}
                                        //else{
                                            //$qty = $detailedProduct->current_stock;
                                        //}
                                    @endphp
                                    <span class="badge badge-md badge-inline badge-pill badge-success">{{ translate('In stock')}}</span>
                                </div>
                            </div>


                            <hr>

                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <small class="mr-2 opacity-50">{{ translate('Sold by')}}: </small><br>
                                    @if ($detailedProduct->added_by == 'seller' && get_setting('vendor_system_activation') == 1)
                                        <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset">{{ $detailedProduct->user->shop->name }}</a>
                                    @else
                                        {{  translate('Inhouse product') }}
                                    @endif
                                </div>
                                @if (get_setting('conversation_system') == 1)
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-soft-primary" onclick="show_chat_modal()">{{ translate('Message Seller')}}</button>
                                    </div>
                                @endif

                                @if ($detailedProduct->brand != null)
                                    <div class="col-auto">
                                        <img src="{{ uploaded_asset($detailedProduct->brand->logo) }}" alt="{{ $detailedProduct->brand->getTranslation('name') }}" height="30">
                                    </div>
                                @endif
                            </div>

                            <hr>

                            @if(home_price($detailedProduct) != home_discounted_price($detailedProduct))

                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ translate('Price')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="fs-20 opacity-60">
                                            <del>
                                                {{ home_price($detailedProduct) }}
                                                @if($detailedProduct->unit != null)
                                                    <span>/{{ $detailedProduct->getTranslation('unit') }}</span>
                                                @endif
                                            </del>
                                        </div>
                                    </div>
                                </div>

                                <div class="row no-gutters mt-2">
                                    <div class="col-2">
                                        <div class="opacity-50">{{ translate('Discount Price')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="">
                                            <strong class="h2 fw-600 text-primary">
                                                {{ home_discounted_price($detailedProduct) }}
                                            </strong>
                                            @if($detailedProduct->unit != null)
                                                <span class="opacity-70">/{{ $detailedProduct->getTranslation('unit') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50">{{ translate('Price')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="">
                                            <strong class="h2 fw-600 text-primary">
                                                {{ home_discounted_price($detailedProduct) }}
                                            </strong>
                                            @if($detailedProduct->unit != null)
                                                <span class="opacity-70">/{{ $detailedProduct->getTranslation('unit') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (addon_is_activated('club_point') && $detailedProduct->earn_point > 0)
                                <div class="row no-gutters mt-4">
                                    <div class="col-2">
                                        <div class="opacity-50">{{  translate('Club Point') }}:</div>
                                    </div>
                                    <div class="col-10">
                                        <div class="d-inline-block club-point bg-soft-base-1 border-light-base-1 border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr>

                            <form id="option-choice-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $detailedProduct->id }}">

                                <div class="row no-gutters pb-3 d-none" id="chosen_price_div">
                                    <div class="col-2">
                                        <div class="opacity-50">{{ translate('Total Price')}}:</div>
                                    </div>
                                    <div class="col-10">
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
                                <button type="button" class="btn btn-soft-primary mr-2 add-to-cart fw-600" onclick="addToCart()">
                                    <i class="las la-shopping-bag"></i>
                                    <span class="d-none d-md-inline-block"> {{ translate('Add to cart')}}</span>
                                </button>
                                <button type="button" class="btn btn-primary buy-now fw-600" onclick="buyNow()">
                                    <i class="la la-shopping-cart"></i> {{ translate('Buy Now')}}
                                </button>
                            </div>



                            <div class="d-table width-100 mt-3">
                                <div class="d-table-cell">
                                    <!-- Add to wishlist button -->
                                    <button type="button" class="btn pl-0 btn-link fw-600" onclick="addToWishList({{ $detailedProduct->id }})">
                                        {{ translate('Add to wishlist')}}
                                    </button>
                                    <!-- Add to compare button -->
                                    <button type="button" class="btn btn-link btn-icon-left fw-600" onclick="addToCompare({{ $detailedProduct->id }})">
                                        {{ translate('Add to compare')}}
                                    </button>


                                    @if(Auth::check() && addon_is_activated('affiliate_system') && (\App\Models\AffiliateOption::where('type', 'product_sharing')->first()->status || \App\Models\AffiliateOption::where('type', 'category_wise_affiliate')->first()->status) && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                                        @php
                                            if(Auth::check()){
                                                if(Auth::user()->referral_code == null){
                                                    Auth::user()->referral_code = substr(Auth::user()->id.Str::random(10), 0, 10);
                                                    Auth::user()->save();
                                                }
                                                $referral_code = Auth::user()->referral_code;
                                                $referral_code_url = URL::to('/product').'/'.$detailedProduct->slug."?product_referral_code=$referral_code";
                                            }
                                        @endphp
                                        <div class="form-group">
                                            <textarea id="referral_code_url" class="form-control" readonly type="text" style="display:none">{{$referral_code_url}}</textarea>
                                        </div>
                                        <button type=button id="ref-cpurl-btn" class="btn btn-sm btn-secondary" data-attrcpy="{{ translate('Copied')}}" onclick="CopyToClipboard('referral_code_url')">{{ translate('Copy the Promote Link')}}</button>
                                    @endif
                                </div>
                            </div>

                            <hr class="mt-2">

                            @php
                                $refund_sticker = get_setting('refund_sticker');
                            @endphp
                            @if (addon_is_activated('refund_request'))
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="opacity-50 mt-2">{{ translate('Refund')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <a href="{{ route('returnpolicy') }}" target="_blank">
                                            @if ($refund_sticker != null)
                                                <img src="{{ uploaded_asset($refund_sticker) }}" height="36">
                                            @else
                                                <img src="{{ static_asset('assets/img/refund-sticker.jpg') }}" height="36">
                                            @endif</a>
                                        <a href="{{ route('returnpolicy') }}" class="ml-2" target="_blank">{{ translate('View Policy') }}</a>
                                    </div>
                                </div>
                            @endif
                            @if ($detailedProduct->added_by == 'seller')
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="product-description-label">{{ translate('Seller Guarantees')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        @if ($detailedProduct->user->shop->verification_status == 1)
                                            {{ translate('Verified seller')}}
                                        @else
                                            {{ translate('Non verified seller')}}
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="row no-gutters mt-4">
                                <div class="col-2">
                                    <div class="opacity-50 mt-2">{{ translate('Share')}}:</div>
                                </div>
                                <div class="col-10">
                                    <div class="aiz-share"></div>
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
            <div class="row">
                <div class="col-xl-3">
                    @if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->shop != null)
                        <div class="bg-white shadow-sm mb-3">
                            <div class="position-relative p-3 text-left">
                                @if ($detailedProduct->user->shop->verification_status)
                                    <div class="absolute-top-right p-2 bg-white z-1">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" viewBox="0 0 287.5 442.2" width="22" height="34">
                                            <polygon style="fill:#F8B517;" points="223.4,442.2 143.8,376.7 64.1,442.2 64.1,215.3 223.4,215.3 "/>
                                            <circle style="fill:#FBD303;" cx="143.8" cy="143.8" r="143.8"/>
                                            <circle style="fill:#F8B517;" cx="143.8" cy="143.8" r="93.6"/>
                                            <polygon style="fill:#FCFCFD;" points="143.8,55.9 163.4,116.6 227.5,116.6 175.6,154.3 195.6,215.3 143.8,177.7 91.9,215.3 111.9,154.3
                                            60,116.6 124.1,116.6 "/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="opacity-50 fs-12 border-bottom">{{ translate('Sold by')}}</div>
                                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="text-reset d-block fw-600">
                                    {{ $detailedProduct->user->shop->name }}
                                    @if ($detailedProduct->user->shop->verification_status == 1)
                                        <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
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
                                    <div class="opacity-60 fs-12">({{ $total }} {{ translate('customer reviews')}})</div>
                                </div>
                            </div>
                            <div class="row no-gutters align-items-center border-top">
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="d-block btn btn-soft-primary rounded-0">{{ translate('Visit Store')}}</a>
                                </div>
                                <div class="col">
                                    <ul class="social list-inline mb-0">
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->facebook }}" class="facebook" target="_blank">
                                                <i class="lab la-facebook-f opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->google }}" class="google" target="_blank">
                                                <i class="lab la-google opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item mr-0">
                                            <a href="{{ $detailedProduct->user->shop->twitter }}" class="twitter" target="_blank">
                                                <i class="lab la-twitter opacity-60"></i>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="{{ $detailedProduct->user->shop->youtube }}" class="youtube" target="_blank">
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
                            {{ translate('Top Selling Products')}}
                        </div>
                        <div class="p-3">
                            <ul class="list-group list-group-flush">
                                @foreach (filter_products(\App\Models\Product::where('user_id', $detailedProduct->user_id)->orderBy('num_of_sale', 'desc'))->limit(6)->get() as $key => $top_product)
                                <li class="py-3 px-0 list-group-item border-light">
                                    <div class="row gutters-10 align-items-center">
                                        <div class="col-5">
                                            <a href="{{ route('product', $top_product->slug) }}" class="d-block text-reset">
                                                <img
                                                    class="img-fit lazyload h-110px"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($top_product->thumbnail_img) }}"
                                                    alt="{{ $top_product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class="col-7">
                                            <h4 class="fs-13 text-truncate-2">
                                                <a href="{{ route('product', $top_product->slug) }}" class="d-block text-reset">{{ $top_product->getTranslation('name') }}</a>
                                            </h4>
                                            <div class="rating rating-sm mt-1">
                                                {{ renderStarRating($top_product->rating) }}
                                            </div>
                                            <div class="mt-2">
                                                <span class="fs-17 fw-600 text-primary">{{ home_discounted_base_price($top_product) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="bg-white mb-3 shadow-sm rounded">
                        <div class="nav border-bottom aiz-nav-tabs">
                            <a href="#tab_default_1" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset active show">{{ translate('Description')}}</a>
                            @if($detailedProduct->video_link != null)
                                <a href="#tab_default_2" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Video')}}</a>
                            @endif
                            @if($detailedProduct->pdf != null)
                                <a href="#tab_default_3" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Downloads')}}</a>
                            @endif
                            <a href="#tab_default_4" data-toggle="tab" class="p-3 fs-16 fw-600 text-reset">{{ translate('Reviews')}}</a>
                        </div>

                        <div class="tab-content pt-0">
                            <div class="tab-pane active show" id="tab_default_1">
                                <div class="p-4">
                                    <div class="mw-100 overflow-auto">
                                        <?php echo $detailedProduct->getTranslation('description'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_default_2">
                                <div class="p-4">
                                    <!-- 16:9 aspect ratio -->
                                    <div class="embed-responsive embed-responsive-16by9 mb-5">
                                        @if ($detailedProduct->video_provider == 'youtube' && isset(explode('=', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ explode('=', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'dailymotion' && isset(explode('video/', $detailedProduct->video_link)[1]))
                                            <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                                        @elseif ($detailedProduct->video_provider == 'vimeo' && isset(explode('vimeo.com/', $detailedProduct->video_link)[1]))
                                            <iframe src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab_default_3">
                                <div class="p-4 text-center">
                                    <a href="{{ uploaded_asset($detailedProduct->pdf) }}"  class="btn btn-primary">{{  translate('Download') }}</a>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab_default_4">
                                <div class="p-4">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($detailedProduct->reviews as $key => $review)
                                            @if($review->user != null)
                                            <li class="media list-group-item d-flex">

                                                <span class="avatar avatar-md mr-3">
                                                    <img
                                                        class="lazyload"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                        @if($review->user->avatar_original !=null)
                                                            data-src="{{ uploaded_asset($review->user->avatar_original) }}"
                                                        @else
                                                            data-src="{{ uploaded_asset('assets/img/placeholder.jpg') }}"
                                                        @endif
                                                    >
                                                </span>
                                                <div class="media-body">
                                                    <div class="d-flex justify-content-between">
                                                        <h3 class="fs-15 fw-600 mb-0">{{ $review->user->name }}</h3>
                                                        <span class="rating rating-sm">
                                                            @for ($i=0; $i < $review->rating; $i++)
                                                                <i class="las la-star active"></i>
                                                            @endfor
                                                            @for ($i=0; $i < 5-$review->rating; $i++)
                                                                <i class="las la-star"></i>
                                                            @endfor
                                                        </span>
                                                    </div>
                                                    <div class="opacity-60 mb-2">{{ date('d-m-Y', strtotime($review->created_at)) }}</div>
                                                    <p class="comment-text">
                                                        {{ $review->comment }}
                                                    </p>
                                                </div>
                                            </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    @if(count($detailedProduct->reviews) <= 0)
                                        <div class="text-center fs-18 opacity-70">
                                            {{  translate('There have been no reviews for this product yet.') }}
                                        </div>
                                    @endif

                                    @if(Auth::check())
                                        @php
                                            $commentable = false;
                                        @endphp
                                        @foreach ($detailedProduct->orderDetails as $key => $orderDetail)
                                            @if($orderDetail->order != null && $orderDetail->order->user_id == Auth::user()->id && $orderDetail->delivery_status == 'delivered' && \App\Models\Review::where('user_id', Auth::user()->id)->where('product_id', $detailedProduct->id)->first() == null)
                                                @php
                                                    $commentable = true;
                                                @endphp
                                            @endif
                                        @endforeach
                                        @if ($commentable)
                                            <div class="pt-4">
                                                <div class="border-bottom mb-4">
                                                    <h3 class="fs-17 fw-600">
                                                        {{ translate('Write a review')}}
                                                    </h3>
                                                </div>
                                                <form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-uppercase c-gray-light">{{ translate('Your name')}}</label>
                                                                <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control" disabled required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-uppercase c-gray-light">{{ translate('Email')}}</label>
                                                                <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" required disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="opacity-60">{{ translate('Rating')}}</label>
                                                        <div class="rating rating-input">
                                                            <label>
                                                                <input type="radio" name="rating" value="1">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="2">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="3">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="4">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="rating" value="5">
                                                                <i class="las la-star"></i>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="opacity-60">{{ translate('Comment')}}</label>
                                                        <textarea class="form-control" rows="4" name="comment" placeholder="{{ translate('Your review')}}" required></textarea>
                                                    </div>

                                                    <div class="text-right">
                                                        <button type="submit" class="btn btn-primary mt-3">
                                                            {{ translate('Submit review')}}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded shadow-sm">
                        <div class="border-bottom p-3">
                            <h3 class="fs-16 fw-600 mb-0 text-left">
                                <span class="">{{ translate('Related products')}}</span>
                            </h3>
                        </div>
                        <div class="p-3">
                            <div class="aiz-carousel gutters-5 half-outside-arrow" data-items="5" data-xl-items="3" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-infinite='true'>
                                @foreach (filter_products(\App\Models\Product::where('category_id', $detailedProduct->category_id)->where('id', '!=', $detailedProduct->id))->limit(10)->get() as $key => $related_product)
                                <div class="carousel-box">
                                    <div class="aiz-card-box border border-light rounded hov-shadow-md my-2 has-transition">
                                        <div class="">
                                            <a href="{{ route('product', $related_product->slug) }}" class="d-block">
                                                <img
                                                    class="img-fit lazyload mx-auto h-140px h-md-210px"
                                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                    data-src="{{ uploaded_asset($related_product->thumbnail_img) }}"
                                                    alt="{{ $related_product->getTranslation('name') }}"
                                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                >
                                            </a>
                                        </div>
                                        <div class="p-md-3 p-2 text-left">
                                            <div class="fs-15">
                                                @if(home_base_price($related_product) != home_discounted_base_price($related_product))
                                                    <del class="fw-600 opacity-50 mr-1">{{ home_base_price($related_product) }}</del>
                                                @endif
                                                <span class="fw-700 text-primary">{{ home_discounted_base_price($related_product) }}</span>
                                            </div>
                                            <div class="rating rating-sm mt-1">
                                                {{ renderStarRating($related_product->rating) }}
                                            </div>
                                            <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0">
                                                <a href="{{ route('product', $related_product->slug) }}" class="d-block text-reset">{{ $related_product->getTranslation('name') }}</a>
                                            </h3>
                                            @if (addon_is_activated('club_point'))
                                                <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                                    {{ translate('Club Point') }}:
                                                    <span class="fw-700 float-right">{{ $related_product->earn_point }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('modal')
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 heading-5">{{ translate('Any question about this product?')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title" value="{{ $detailedProduct->getTranslation('name') }}" placeholder="{{ translate('Product Name') }}" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600" data-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Send')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-600">{{ translate('Login')}}</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                @if (addon_is_activated('otp_system'))
                                    <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone')}}" name="email" id="email">
                                @else
                                    <input type="email" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                @endif
                                @if (addon_is_activated('otp_system'))
                                    <span class="opacity-60">{{  translate('Use country code before number') }}</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control h-auto form-control-lg" placeholder="{{ translate('Password')}}">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span class=opacity-60>{{  translate('Remember Me') }}</span>
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                                <div class="col-6 text-right">
                                    <a href="{{ route('password.request') }}" class="text-reset opacity-60 fs-14">{{ translate('Forgot password?')}}</a>
                                </div>
                            </div>

                            <div class="mb-5">
                                <button type="submit" class="btn btn-primary btn-block fw-600">{{  translate('Login') }}</button>
                            </div>
                        </form>

                        <div class="text-center mb-3">
                            <p class="text-muted mb-0">{{ translate('Dont have an account?')}}</p>
                            <a href="{{ route('user.registration') }}">{{ translate('Register Now')}}</a>
                        </div>
                        @if(get_setting('google_login') == 1 ||
                            get_setting('facebook_login') == 1 ||
                            get_setting('twitter_login') == 1 ||
                            get_setting('apple_login') == 1)
                            <div class="separator mb-3">
                                <span class="bg-white px-3 opacity-60">{{ translate('Or Login With')}}</span>
                            </div>
                            <ul class="list-inline social colored text-center mb-5">
                                @if (get_setting('facebook_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="facebook">
                                            <i class="lab la-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if(get_setting('google_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'google']) }}" class="google">
                                            <i class="lab la-google"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('twitter_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="twitter">
                                            <i class="lab la-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                                @if (get_setting('apple_login') == 1)
                                    <li class="list-inline-item">
                                        <a href="{{ route('social.login', ['provider' => 'apple']) }}" class="apple">
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
    		$('#share').share({
    			showLabel: false,
                showCount: false,
                shares: ["email", "twitter", "facebook", "linkedin", "pinterest", "stumbleupon", "whatsapp"]
    		});
    	});

        function CopyToClipboard(containerid) {
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(document.getElementById(containerid));
                range.select().createTextRange();
                document.execCommand("Copy");

            } else if (window.getSelection) {
                var range = document.createRange();
                document.getElementById(containerid).style.display = "block";
                range.selectNode(document.getElementById(containerid));
                window.getSelection().addRange(range);
                document.execCommand("Copy");
                document.getElementById(containerid).style.display = "none";

            }
            showFrontendAlert('success', 'Copied');
        }

        function show_chat_modal(){
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

    </script>
@endsection
