<div class="row">
    <div class="col-xl-6">
        @php
            $subtotal = 0;
            $tax = 0;
        @endphp
        @if (Session::has('pos.cart'))
            <ul class="list-group list-group-flush">
                @forelse (Session::get('pos.cart') as $key => $cartItem)
                @php
	                $subtotal += $cartItem['price']*$cartItem['quantity'];
	                $tax += $cartItem['tax']*$cartItem['quantity'];
                    $stock = \App\Models\ProductStock::find($cartItem['stock_id']);
                @endphp
                <li class="list-group-item px-0">
                    <div class="row gutters-10 align-items-center">
                        <div class="col">
                            <div class="d-flex">
                                @if($stock->image == null)
                                    <img src="{{ uploaded_asset($stock->product->thumbnail_img) }}" class="img-fit size-60px">
                                @else
                                    <img src="{{ uploaded_asset($stock->image) }}" class="img-fit size-60px">
                                @endif
                                <span class="flex-grow-1 ml-3 mr-0">
                                    <div class="text-truncate-2">{{ $stock->product->name }}</div>
                                    <span class="span badge badge-inline fs-12 badge-soft-secondary">{{ $cartItem['variant'] }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="fs-14 fw-600 text-right">{{ single_price($cartItem['price']) }}</div>
                            <div class="fs-14 text-right">{{ translate('QTY') }}: {{ $cartItem['quantity'] }}</div>
                        </div>
                    </div>
                    <!-- Show Addons if available -->
                    @if (!empty($cartItem['addons']))
                        <ul class="list-unstyled mt-2">
                            @foreach ($cartItem['addons'] as $addon)
                                <li class="d-flex justify-content-between">
                                    <span>{{ $addon['name'] }}</span>
                                    <span>{{ single_price($addon['price']) }} x {{ $addon['quantity'] }}</span>
                                    @php
                                        $subtotal += $addon['price'] * $addon['quantity'];
                                    @endphp
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @empty
                <li class="list-group-item">
                    <div class="text-center">
                        <i class="las la-frown la-3x opacity-50"></i>
                        <p>{{ translate('No Product Added') }}</p>
                    </div>
                </li>
            @endforelse
            </ul>
        @else
            <div class="text-center">
                <i class="las la-frown la-3x opacity-50"></i>
                <p>{{ translate('No Product Added') }}</p>
            </div>
        @endif
    </div>
    <div class="col-xl-6">
        <div class="pl-xl-4">
            <div class="card mb-4">
                <div class="card-header"><span class="fs-16">{{ translate('Customer Info') }}</span></div>
                <div class="card-body">
                    @if(Session::has('pos.shipping_info') && Session::get('pos.shipping_info')['name'] != null)
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{translate('Type')}}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['address_type'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{translate('Name')}}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['name'] }}</span>
                        </div>

                        @if(!empty(Session::get('pos.shipping_info')['email']))
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{translate('Email')}}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['email'] }}</span>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{translate('Phone')}}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['phone'] }}</span>
                        </div>

                        @if(!empty(Session::get('pos.shipping_info')['state']))
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{translate('State')}}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['state'] }}</span>
                        </div>
                        @endif

                        @if(!empty(Session::get('pos.shipping_info')['city']))
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{translate('City')}}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['city'] }}</span>
                        </div>
                        @endif

                        @if(!empty(Session::get('pos.shipping_info')['bloc']))
                        <div class="d-flex justify-content-between  mb-2">
                            <span class="">{{translate('Block')}}:</span>
                            <span class="fw-600">{{ Session::get('pos.shipping_info')['bloc'] }}</span>
                        </div>
                        @endif

                        <!-- avenue -->
                        @if(!empty(Session::get('pos.shipping_info')['avenue']))
                            <div class="d-flex justify-content-between  mb-2">
                                <span class="">{{translate('Avenue')}}:</span>
                                <span class="fw-600">{{ Session::get('pos.shipping_info')['avenue'] }}</span>
                            </div>
                        @endif
                        <!-- street -->
                        @if(!empty(Session::get('pos.shipping_info')['street']))
                            <div class="d-flex justify-content-between  mb-2">
                                <span class="">{{translate('Street')}}:</span>
                                <span class="fw-600">{{ Session::get('pos.shipping_info')['street'] }}</span>
                            </div>
                        @endif
                        <!-- building -->
                        @if(!empty(Session::get('pos.shipping_info')['building_number']))
                            <div class="d-flex justify-content-between  mb-2">
                                <span class="">{{translate('Building')}}:</span>
                                <span class="fw-600">{{ Session::get('pos.shipping_info')['building_number'] }}</span>
                            </div>
                        @endif
                        <!-- floor -->
                        @if(!empty(Session::get('pos.shipping_info')['floor']))
                            <div class="d-flex justify-content-between  mb-2">
                                <span class="">{{translate('Floor')}}:</span>
                                <span class="fw-600">{{ Session::get('pos.shipping_info')['floor'] }}</span>
                            </div>
                        @endif
                        <!-- apartment -->
                        @if(!empty(Session::get('pos.shipping_info')['apt_number']))
                            <div class="d-flex justify-content-between  mb-2">
                                <span class="">{{translate('Apartment')}}:</span>
                                <span class="fw-600">{{ Session::get('pos.shipping_info')['apt_number'] }}</span>
                            </div>
                        @endif
                        <!-- House Number -->
                        @if(!empty(Session::get('pos.shipping_info')['house']))
                            <div class="d-flex justify-content-between  mb-2">
                                <span class="">{{translate('House Number')}}:</span>
                                <span class="fw-600">{{ Session::get('pos.shipping_info')['house'] }}</span>
                            </div>
                        @endif
                        <!-- Office Number -->
                        @if(!empty(Session::get('pos.shipping_info')['office_number']))
                            <div class="d-flex justify-content-between  mb-2">
                                <span class="">{{translate('Office Number')}}:</span>
                                <span class="fw-600">{{ Session::get('pos.shipping_info')['office_number'] }}</span>
                            </div>
                        @endif
                    @else
                        <div class="text-center p-4">
                            {{ translate('No customer information selected.') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                <span>{{translate('Total')}}</span>
                <span>{{ single_price($subtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                <span>{{translate('Tax')}}</span>
                <span>{{ single_price($tax) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                <span>{{translate('Shipping')}}</span>
                <span>{{ single_price(Session::get('pos.shipping', 0)) }}</span>
            </div>
            <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                <span>{{translate('Discount')}}</span>
                <span>{{ Session::get('pos.discount_type') == 'flat' ? single_price(Session::get('pos.discount', 0)) : Session::get('pos.discount', 0).'%' }} ({{ Session::get('pos.discount_type') == 'flat' ? translate('Fixed') : translate('Percentage') }})</span>
            </div>
            @php
                $grand_total = (float)$subtotal + (float)$tax + (float)Session::get('pos.shipping', 0);

                // foreach (Session::get('pos.cart') as $cartItem) {
                //     if (!empty($cartItem['addons'])) {
                //         foreach ($cartItem['addons'] as $addon) {
                //             $grand_total += (float)$addon['price'] * (int)$addon['quantity'];
                //         }
                //     }
                // }

                if (Session::get('pos.discount_type') == 'flat') {
                    $grand_total -= (float)Session::get('pos.discount', 0);
                } else {
                    $grand_total -= $grand_total * (float)Session::get('pos.discount', 0) / 100;
                }
            @endphp
            <div class="d-flex justify-content-between fw-600 fs-18 border-top pt-2">
                <span>{{translate('Total')}}</span>
                <span>{{ single_price($grand_total) }}</span>
            </div>
        </div>
    </div>
</div>
