<div class="aiz-pos-cart-list mb-4 mt-3 c-scrollbar-light">
    @php
        $subtotal = 0;
        $tax      = 0;
    @endphp

    @if (Session::has('pos.cart'))
        <ul class="list-group list-group-flush">
            @forelse (Session::get('pos.cart') as $key => $cartItem)
                @php
                    $subtotal += $cartItem['price'] * $cartItem['quantity'];
                    $tax      += $cartItem['tax']   * $cartItem['quantity'];
                    $stock     = \App\Models\ProductStock::find($cartItem['stock_id']);
                @endphp

                <li class="list-group-item py-0 pl-2">
                    <div class="row gutters-5 align-items-center">
                        <!-- Quantity controls -->
                        <div class="col-auto w-60px">
                            <div class="row no-gutters align-items-center flex-column aiz-plus-minus">
                                <button class="btn col-auto btn-icon btn-sm fs-15" type="button"
                                        data-type="plus" data-field="qty-{{ $key }}">
                                    <i class="las la-plus"></i>
                                </button>
                                <input type="text"
                                       name="qty-{{ $key }}"
                                       id="qty-{{ $key }}"
                                       class="col border-0 text-center flex-grow-1 fs-16 input-number"
                                       placeholder="1"
                                       value="{{ $cartItem['quantity'] }}"
                                       min="{{ $stock->product->min_qty }}"
                                       max="{{ $stock->qty }}"
                                       onchange="updateQuantity({{ $key }})">
                                <button class="btn col-auto btn-icon btn-sm fs-15" type="button"
                                        data-type="minus" data-field="qty-{{ $key }}">
                                    <i class="las la-minus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Product Name & Variant -->
                        <div class="col">
                            <div class="text-truncate-2">
                                {{ $stock->product->name }}
                            </div>
                            @if($cartItem['variant'])
                                <span class="badge badge-inline fs-12 badge-soft-secondary">
                                    {{ $cartItem['variant'] }}
                                </span>
                            @endif

                            <!-- SHOW ADDONS HERE -->
                            @if (!empty($cartItem['addons']))
                                <div class="mt-2 ml-3">
                                    <strong>{{ translate('Addons') }}:</strong>
                                    <ul class="list-unstyled m-0 p-0">
                                        @php
                                            $addon_price = 0;
                                        @endphp
                                        @foreach($cartItem['addons'] as $addonKey => $addon)
                                            @php
                                                $addon_price += $addon['price'] * $addon['quantity'];
                                            @endphp
                                            <li class="small text-muted d-flex align-items-center mb-1">
                                                <!-- Addon name -->
                                                <span class="mr-2">{{ $addon['name'] }}</span>
                                                <!-- Price each -->
                                                @if($addon['price'])
                                                    <span class="text-info ml-1">(+{{ single_price($addon['price']) }} {{ translate('each') }})</span>
                                                @endif
                
                                                <!-- Addon quantity controls -->
                                                <div class="input-group input-group-sm ml-3" style="width:120px;">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-light" type="button"
                                                                onclick="updateAddonQty({{ $key }}, {{ $addon['id'] }}, -1)">
                                                            -
                                                        </button>
                                                    </div>
                                                    <input type="text"
                                                        class="form-control text-center addon-qty"
                                                        id="addon_qty_{{ $key }}_{{ $addon['id'] }}"
                                                        value="{{ $addon['quantity'] }}"
                                                        readonly>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-light" type="button"
                                                                onclick="updateAddonQty({{ $key }}, {{ $addon['id'] }}, 1)">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @php
                                    $subtotal += $addon_price; 
                                @endphp
                            @endif
                        </div>

                        <!-- Price Calculation -->
                        <div class="col-auto">
                            <div class="fs-12 opacity-60">
                                {{ single_price($cartItem['price']) }} x {{ $cartItem['quantity'] }}
                            </div>
                            <div class="fs-15 fw-600">
                                {{ single_price(($cartItem['price'] * $cartItem['quantity']) + (!empty($cartItem['addons']) ? $addon_price : 0)) }} 
                            </div>
                        </div>

                        <!-- Remove -->
                        <div class="col-auto">
                            <button type="button"
                                    class="btn btn-circle btn-icon btn-sm btn-soft-danger ml-2 mr-0"
                                    onclick="removeFromCart({{ $key }})">
                                <i class="las la-trash-alt"></i>
                            </button>
                        </div>
                    </div>
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

<!-- CART TOTALS -->
@php
    $discount_amount = 0;
    if(Session::has('pos.discount')) {
        if(Session::get('pos.discount_type') == 'flat'){
            $discount_amount = Session::get('pos.discount');
        } else {
            // Percentage
            $discount_amount = ($subtotal + $tax) * Session::get('pos.discount', 0) / 100;
        }
    }
@endphp

<div>
    <!-- Subtotal -->
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ translate('Sub Total') }}</span>
        <span>{{ single_price($subtotal) }}</span>
    </div>

    <!-- Tax -->
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ translate('Tax') }}</span>
        <span>{{ single_price($tax) }}</span>
    </div>

    <!-- Shipping -->
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ translate('Shipping') }}</span>
        <span>{{ single_price(Session::get('pos.shipping', 0)) }}</span>
    </div>

    <!-- Discount -->
    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
        <span>{{ translate('Discount') }}</span>
        @if(Session::get('pos.discount_type') == 'flat')
            <span>{{ single_price(Session::get('pos.discount', 0)) }} ({{ translate('Fixed') }})</span>
        @else
            <span>{{ Session::get('pos.discount', 0).'%' }} ({{ translate('Percentage') }})</span>
        @endif
    </div>

    <!-- Total -->
    <div class="d-flex justify-content-between fw-600 fs-18 border-top pt-2">
        <span>{{ translate('Total') }}</span>
        <span>
            {{
                single_price(
                    (float)$subtotal +
                    (float)$tax +
                    (float)Session::get('pos.shipping', 0)
                    - (float)$discount_amount
                )
            }}
        </span>
    </div>
</div>
