@extends('backend.layouts.app')

@section('content')
    <section class="">
        <form action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row gutters-5">
                <!-- =========================
                         LEFT SIDE (Product List)
                    ========================== -->
                <div class="col-md">
                    <div class="row gutters-5 mb-3">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="form-group mb-0">
                                <input class="form-control form-control-lg" type="text" name="keyword"
                                    placeholder="{{ translate('Search by Product Name/Barcode') }}"
                                    onkeyup="filterProducts()">
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <select name="poscategory" class="form-control form-control-lg aiz-selectpicker"
                                data-live-search="true" onchange="filterProducts()">
                                <option value="">
                                    {{ translate('All Categories') }}
                                </option>
                                @foreach (\App\Models\Category::all() as $category)
                                    <option value="category-{{ $category->id }}">
                                        {{ $category->getTranslation('name') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <select name="brand" class="form-control form-control-lg aiz-selectpicker"
                                data-live-search="true" onchange="filterProducts()">
                                <option value="">
                                    {{ translate('All Brands') }}
                                </option>
                                @foreach (\App\Models\Brand::all() as $brand)
                                    <option value="{{ $brand->id }}">
                                        {{ $brand->getTranslation('name') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Product List Container -->
                    <div class="aiz-pos-product-list c-scrollbar-light">
                        <div class="d-flex flex-wrap justify-content-center" id="product-list">
                            <!-- Filled by AJAX in filterProducts() -->
                        </div>
                        <div id="load-more" class="text-center">
                            <div class="fs-14 d-inline-block fw-600 btn btn-soft-primary c-pointer"
                                onclick="loadMoreProduct()">
                                {{ translate('Loading..') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =========================
                         RIGHT SIDE (Cart & Footer)
                    ========================== -->
                <div class="col-md-auto w-md-350px w-lg-400px w-xl-500px">
                    <div class="card mb-3">
                        <div class="card-body">
                            <!-- Customer selection -->
                            <div class="d-flex border-bottom pb-3">
                                <div class="flex-grow-1">
                                    <select name="user_id" class="form-control aiz-selectpicker pos-customer"
                                        data-live-search="true" onchange="getShippingAddress()">
                                        <option value="">
                                            {{ translate('Walk In Customer') }}
                                        </option>
                                        <option value="online">
                                            {{ translate('Online order') }}
                                        </option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" data-contact="{{ $customer->email }}">
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-icon btn-soft-dark ml-3 mr-0"
                                    data-target="#new-customer" data-toggle="modal">
                                    <i class="las la-truck"></i>
                                </button>
                            </div>

                            <!-- Cart details -->
                            <div id="cart-details">
                                <div class="aiz-pos-cart-list mb-4 mt-3 c-scrollbar-light">
                                    @php
                                        $subtotal = 0;
                                        $tax = 0;
                                    @endphp
                                    @if (Session::has('pos.cart'))
                                        <ul class="list-group list-group-flush">
                                            @forelse (Session::get('pos.cart') as $key => $cartItem)
                                                @php
                                                    $subtotal += $cartItem['price'] * $cartItem['quantity'];
                                                    $tax += $cartItem['tax'] * $cartItem['quantity'];
                                                    $stock = \App\Models\ProductStock::find($cartItem['stock_id']);
                                                @endphp
                                                <li class="list-group-item py-0 pl-2">
                                                    <div class="row gutters-5 align-items-center">
                                                        <!-- Quantity Up/Down -->
                                                        <div class="col-auto w-60px">
                                                            <div
                                                                class="row no-gutters align-items-center flex-column aiz-plus-minus">
                                                                <button class="btn col-auto btn-icon btn-sm fs-15"
                                                                    type="button" data-type="plus"
                                                                    data-field="qty-{{ $key }}">
                                                                    <i class="las la-plus"></i>
                                                                </button>
                                                                <input type="text" name="qty-{{ $key }}"
                                                                    id="qty-{{ $key }}"
                                                                    class="col border-0 text-center flex-grow-1 fs-16 input-number"
                                                                    placeholder="1" value="{{ $cartItem['quantity'] }}"
                                                                    min="{{ $stock->product->min_qty }}"
                                                                    max="{{ $stock->qty }}"
                                                                    onchange="updateQuantity({{ $key }})">
                                                                <button class="btn col-auto btn-icon btn-sm fs-15"
                                                                    type="button" data-type="minus"
                                                                    data-field="qty-{{ $key }}">
                                                                    <i class="las la-minus"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Product Name / Variant -->
                                                        <div class="col">
                                                            <div class="text-truncate-2">
                                                                {{ $stock->product->name }}
                                                            </div>
                                                            <span
                                                                class="span badge badge-inline fs-12 badge-soft-secondary">
                                                                {{ $cartItem['variant'] }}
                                                            </span>

                                                            <!-- If we have addons (already stored in session) -->
                                                            @if (!empty($cartItem['addons']))
                                                                <div class="mt-2 ml-3">
                                                                    <strong>{{ translate('Addons') }}:</strong>
                                                                    <ul class="list-unstyled m-0 p-0">
                                                                        @php
                                                                            $addon_price = 0;
                                                                        @endphp
                                                                        @foreach ($cartItem['addons'] as $addonKey => $addon)
                                                                            @php
                                                                                $addon_price +=
                                                                                    $addon['price'] *
                                                                                    $addon['quantity'];
                                                                            @endphp
                                                                            <li
                                                                                class="small text-muted d-flex align-items-center mb-1">
                                                                                <!-- Addon name -->
                                                                                <span
                                                                                    class="mr-2">{{ $addon['name'] }}</span>
                                                                                <!-- Price each -->
                                                                                @if ($addon['price'])
                                                                                    <span
                                                                                        class="text-info ml-1">(+{{ single_price($addon['price']) }}
                                                                                        {{ translate('each') }})</span>
                                                                                @endif

                                                                                <!-- Addon quantity controls -->
                                                                                <div class="input-group input-group-sm ml-3"
                                                                                    style="width:120px;">
                                                                                    <div class="input-group-prepend">
                                                                                        <button class="btn btn-light"
                                                                                            type="button"
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
                                                                                        <button class="btn btn-light"
                                                                                            type="button"
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

                                                        <!-- Price -->
                                                        <div class="col-auto">
                                                            <div class="fs-12 opacity-60">
                                                                {{ single_price($cartItem['price']) }} x
                                                                {{ $cartItem['quantity'] }}
                                                            </div>
                                                            <div class="fs-15 fw-600">
                                                                {{ single_price($cartItem['price'] * $cartItem['quantity'] + (!empty($cartItem['addons']) ? $addon_price : 0)) }}
                                                            </div>
                                                        </div>

                                                        <!-- Remove from cart -->
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

                                <!-- Totals -->
                                @php
                                    $discount_session = Session::get('pos.discount', 0);
                                    $discount_type = Session::get('pos.discount_type', 'flat');
                                    $shipping_cost = Session::get('pos.shipping', 0);
                                @endphp

                                <div>
                                    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                        <span>{{ translate('Sub Total') }}</span>
                                        <span>{{ single_price($subtotal) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                        <span>{{ translate('Tax') }}</span>
                                        <span>{{ single_price($tax) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                        <span>{{ translate('Shipping') }}</span>
                                        <span>{{ single_price($shipping_cost) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-600 mb-2 opacity-70">
                                        <span>{{ translate('Discount') }}</span>
                                        @if ($discount_type == 'flat')
                                            <span>{{ single_price($discount_session) }}</span>
                                        @else
                                            <span>{{ $discount_session . '%' }}</span>
                                        @endif
                                    </div>

                                    @php
                                        // If discount is flat:
                                        $discount_amount = 0;
                                        if ($discount_type == 'flat') {
                                            $discount_amount = $discount_session;
                                        } else {
                                            // percent
                                            $discount_amount = (($subtotal + $tax) * $discount_session) / 100;
                                        }
                                    @endphp

                                    <div class="d-flex justify-content-between fw-600 fs-18 border-top pt-2">
                                        <span>{{ translate('Total') }}</span>
                                        <span>
                                            {{ single_price($subtotal + $tax + $shipping_cost - $discount_amount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                             POS Footer (Discount/Shipp)
                        ========================== -->
                    <div class="pos-footer mar-btm">
                        <div class="dropdown dropup m-1">
                            <div>
                                <div class="input-group d-flex justify-content-between">
                                    <input type="number" min="0" placeholder="Amount" name="discount"
                                        class="form-control col-4" value="{{ Session::get('pos.discount', 0) }}"
                                        required>
                                    <div class="input-group-append">
                                        <select class="form-control" name="discount_type">
                                            <option value="flat"
                                                {{ Session::get('pos.discount_type', 'flat') == 'flat' ? 'selected' : '' }}>
                                                {{ translate('Flat') }}
                                            </option>
                                            <option value="percentage"
                                                {{ Session::get('pos.discount_type', 'flat') == 'percentage' ? 'selected' : '' }}>
                                                {{ translate('Percentage') }}
                                            </option>
                                        </select>
                                    </div>
                                    <button class="btn btn-outline-dark btn-styled" type="button"
                                        onclick="setDiscount()">
                                        {{ translate('Discount') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between">
                            <div class="d-flex">
                                <div class="dropdown mr-3 ml-0 dropup">
                                    <button class="btn btn-outline-dark btn-styled dropdown-toggle" type="button"
                                        data-toggle="dropdown">
                                        {{ translate('Shipping') }}
                                    </button>
                                    <div class="dropdown-menu p-3 dropdown-menu-lg">
                                        <div class="input-group">
                                            <input type="number" min="0" placeholder="Amount" name="shipping"
                                                class="form-control" value="{{ Session::get('pos.shipping', 0) }}"
                                                required onchange="setShipping()">
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    {{ translate('Flat') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Place Order Button -->
                            <div class="my-2 my-md-0">
                                <button type="button" class="btn btn-primary btn-block" onclick="orderConfirmation()">
                                    {{ translate('Place Order') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('modal')
    <!-- =========== Existing Modals (Address, etc.) =========== -->
    <!-- Address Modal -->
    <div id="new-customer" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6" id="addressTitle">
                        {{ translate('Shipping Address') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="shipping_form">
                    <div class="modal-body" id="shipping_address">
                        <!-- Filled by getShippingAddress() -->
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-styled btn-base-3" data-dismiss="modal" id="close-button">
                        {{ translate('Close') }}
                    </button>
                    <button type="button" class="btn btn-primary btn-styled btn-base-1" id="confirm-address"
                        data-dismiss="modal">
                        {{ translate('Confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- new address modal -->
    <div id="new-address-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">
                        {{ translate('Shipping Address') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form class="form-horizontal" action="{{ route('addresses.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="customer_id" id="set_customer_id" value="">
                        <!-- Address fields... -->
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-2 control-label" for="address">
                                    {{ translate('Address') }}
                                </label>
                                <div class="col-sm-10">
                                    <textarea placeholder="{{ translate('Address') }}" id="address" name="address" class="form-control" required></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- country, state, city, postal_code, phone, etc. -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-styled btn-base-3" data-dismiss="modal">
                            {{ translate('Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary btn-styled btn-base-1">
                            {{ translate('Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Confirm Modal -->
    <div id="order-confirm" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom modal-xl">
            <div class="modal-content" id="variants">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">
                        {{ translate('Order Summary') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body" id="order-confirmation">
                    <div class="p-4 text-center">
                        <i class="las la-spinner la-spin la-3x"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-base-3" data-dismiss="modal">
                        {{ translate('Close') }}
                    </button>
                    <button type="button" onclick="oflinePayment()" class="btn btn-base-1 btn-warning">
                        {{ translate('Visa Payment') }}
                    </button>
                    <button type="button" onclick="submitOrder('cash_on_delivery')" class="btn btn-base-1 btn-info">
                        {{ translate('Confirm with COD') }}
                    </button>
                    <button type="button" onclick="submitOrder('cash')" class="btn btn-base-1 btn-success">
                        {{ translate('Confirm with Cash') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Offline Payment Modal -->
    <div id="offlin_payment" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header bord-btm">
                    <h4 class="modal-title h6">
                        {{ translate('Offline Payment Info') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Payment Method -->
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label" for="offline_payment_method">
                                {{ translate('Payment Method') }}
                            </label>
                            <div class="col-sm-9">
                                <input placeholder="{{ translate('Name') }}" id="offline_payment_method"
                                    name="offline_payment_method" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <!-- Amount -->
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label" for="offline_payment_amount">
                                {{ translate('Amount') }}
                            </label>
                            <div class="col-sm-9">
                                <input placeholder="{{ translate('Amount') }}" id="offline_payment_amount"
                                    name="offline_payment_amount" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <!-- Transaction ID -->
                    <div class="row">
                        <label class="col-sm-3 control-label" for="trx_id">
                            {{ translate('Transaction ID') }}
                        </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control mb-3" id="trx_id" name="trx_id"
                                placeholder="{{ translate('Transaction ID') }}" required>
                        </div>
                    </div>
                    <!-- Payment Proof field, if you want -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-base-3" data-dismiss="modal">
                        {{ translate('Close') }}
                    </button>
                    <button type="button" onclick="submitOrder('offline_payment')"
                        class="btn btn-styled btn-base-1 btn-success">
                        {{ translate('Confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== ADDON MODAL ========== -->
    <!-- If your product has addons, we show this modal. We will fill it dynamically by JS or partial. -->
    <!-- ADDON MODAL -->
    <div class="modal fade" id="addon-modal" tabindex="-1" role="dialog" aria-labelledby="addon-modal-label">
        <!-- "modal-lg" for extra width if desired -->
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom modal-lg" role="document">
            <div class="modal-content">

                <!-- HEADER: Only has the title + top-right "X" -->
                <div class="modal-header bord-btm">
                    <h5 class="modal-title h6 mb-0">
                        {{ translate('Select Addons') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- BODY: We'll store stock_id + show the addon list here -->
                <div class="modal-body p-3" id="addon-modal-body">
                    <input type="hidden" id="addon_stock_id" value="">
                    <div id="addon-items">
                        <!-- Filled by AJAX in openAddonModal(...) -->
                    </div>
                </div>

                <!-- FOOTER: Only "Add to Cart" button -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="confirmAddons()">
                        {{ translate('Add to Cart') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/epos.js') }}"></script>

    <script type="text/javascript">
        var products = null;

        $(document).ready(function() {
            printInvoice();
            $('body').addClass('side-menu-closed');

            // Detect click on the "plus" overlay
            $('#product-list').on('click', '.add-plus:not(.c-not-allowed)', function() {
                var stock_id = $(this).data('stock-id');
                var has_addons = $(this).data('has-addons'); // "1" or "0"

                if (has_addons == '1') {
                    // If this product has addons, show the Addons Modal
                    openAddonModal(stock_id);
                } else {
                    // If no addons, add directly
                    $.post('{{ route('pos.addToCart') }}', {
                        _token: AIZ.data.csrf,
                        stock_id: stock_id
                    }, function(data) {
                        if (data.success == 1) {
                            updateCart(data.view);
                        } else {
                            AIZ.plugins.notify('danger', data.message);
                        }
                    });
                }
            });

            filterProducts();
            getShippingAddress();
        });

        function openAddonModal(stock_id) {
            // Clear old data
            $("#addon-items").html('');
            $("#addon_stock_id").val(stock_id);

            // Example AJAX to load the addon form HTML
            $.post("{{ route('pos.show_addon_modal') }}", {
                _token: AIZ.data.csrf,
                stock_id: stock_id
            }, function(html) {
                // Insert the HTML (but *do not* include another add-to-cart or close button there)
                $("#addon-items").html(html);
                $("#addon-modal").modal('show');
            });
        }

        function confirmAddons() {
            var stock_id = $("#addon_stock_id").val();
            var addonsData = [];

            // Gather the selected addons
            $(".addon-quantity").each(function() {
                var qty = parseInt($(this).val());
                if (qty > 0) {
                    addonsData.push({
                        id: $(this).data('addon-id'),
                        price: $(this).data('addon-price'),
                        quantity: qty
                    });
                }
            });

            // Close the modal
            $("#addon-modal").modal('hide');

            // Now send to addToCart
            $.post('{{ route('pos.addToCart') }}', {
                _token: AIZ.data.csrf,
                stock_id: stock_id,
                addons: addonsData
            }, function(data) {
                if (data.success == 1) {
                    updateCart(data.view);
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            });
        }


        function updateCart(data) {
            $('#cart-details').html(data);
            AIZ.extra.plusMinus();
        }

        function filterProducts() {
            var keyword = $('input[name=keyword]').val();
            var category = $('select[name=poscategory]').val();
            var brand = $('select[name=brand]').val();

            $.get('{{ route('pos.search_product') }}', {
                keyword: keyword,
                category: category,
                brand: brand
            }, function(data) {
                products = data;
                $('#product-list').html('');
                setProductList(data);
            });
        }

        function loadMoreProduct() {
            if (products != null && products.links.next != null) {
                $('#load-more').find('.btn').html('{{ translate('Loading..') }}');
                $.get(products.links.next, {}, function(data) {
                    products = data;
                    setProductList(data);
                });
            }
        }

        function setProductList(data) {
            for (var i = 0; i < data.data.length; i++) {
                // We assume your search returns "has_addons" = 1 or 0
                var has_addons = data.data[i].has_addons ? '1' : '0';

                $('#product-list').append(`
                <div class="w-140px w-xl-180px w-xxl-210px mx-2">
                    <div class="card bg-white c-pointer product-card hov-container">
                        <div class="position-relative">
                            <span class="absolute-top-left mt-1 ml-1 mr-0">
                                ${
                                    data.data[i].qty > 0
                                    ? '<span class="badge badge-inline badge-success fs-13">{{ translate('In stock') }}: '+data.data[i].qty+'</span>'
                                    : '<span class="badge badge-inline badge-danger fs-13">{{ translate('Out of stock') }}: '+data.data[i].qty+'</span>'
                                }
                            </span>
                            ${
                                data.data[i].variant != null
                                ? '<span class="badge badge-inline badge-warning absolute-bottom-left mb-1 ml-1 mr-0 fs-13 text-truncate">'+data.data[i].variant+'</span>'
                                : ''
                            }
                            <img src="${data.data[i].thumbnail_image}"
                                 class="card-img-top img-fit h-120px h-xl-180px h-xxl-210px mw-100 mx-auto">
                        </div>
                        <div class="card-body p-2 p-xl-3">
                            <div class="text-truncate fw-600 fs-14 mb-2">
                                ${data.data[i].name}
                            </div>
                            <div>
                                ${
                                    data.data[i].price != data.data[i].base_price
                                    ? '<del class="mr-2 ml-0">'+data.data[i].base_price+'</del><span>'+data.data[i].price+'</span>'
                                    : '<span>'+data.data[i].base_price+'</span>'
                                }
                            </div>
                        </div>
                        <div class="add-plus absolute-full rounded overflow-hidden hov-box
                            ${data.data[i].qty <= 0 ? 'c-not-allowed' : ''}"
                            data-stock-id="${data.data[i].stock_id}"
                            data-has-addons="${has_addons}">
                            <div class="absolute-full bg-dark opacity-50"></div>
                            <i class="las la-plus absolute-center la-6x text-white"></i>
                        </div>
                    </div>
                </div>
            `);
            }

            if (data.links.next != null) {
                $('#load-more').find('.btn').html('{{ translate('Load More.') }}');
            } else {
                $('#load-more').find('.btn').html('{{ translate('Nothing more found.') }}');
            }
        }

        // Remove item from cart
        function removeFromCart(key) {
            $.post('{{ route('pos.removeFromCart') }}', {
                _token: AIZ.data.csrf,
                key: key
            }, function(data) {
                updateCart(data);
            });
        }

        // Increase/Decrease quantity
        function updateQuantity(key) {
            $.post('{{ route('pos.updateQuantity') }}', {
                _token: AIZ.data.csrf,
                key: key,
                quantity: $('#qty-' + key).val()
            }, function(data) {
                if (data.success == 1) {
                    updateCart(data.view);
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            });
        }

        // Set discount
        function setDiscount() {
            var discount = $('input[name=discount]').val();
            var discount_type = $('select[name=discount_type]').val();

            $.post('{{ route('pos.setDiscount') }}', {
                _token: AIZ.data.csrf,
                discount: discount,
                discount_type: discount_type
            }, function(data) {
                $('#cart-details').html(data);
                AIZ.extra.plusMinus();
            });
        }

        // Set shipping
        function setShipping() {
            var shipping = $('input[name=shipping]').val();
            $.post('{{ route('pos.setShipping') }}', {
                _token: AIZ.data.csrf,
                shipping: shipping
            }, function(data) {
                updateCart(data);
            });
        }

        // Shipping address
        function getShippingAddress() {
            $.post('{{ route('pos.getShippingAddress') }}', {
                _token: AIZ.data.csrf,
                id: $('select[name=user_id]').val()
            }, function(data) {
                $('#shipping_address').html(data);
                if (data.typeOfAddress == 'walk_in_customer') {
                    $('#addressTitle').html('{{ translate('Customer info') }}');
                } else {
                    $('#addressTitle').html('{{ translate('Shipping Address') }}');
                }
                // If you want to auto-load states
                get_states(117);
            });
        }

        $("#confirm-address").click(function() {
            var data = new FormData($('#shipping_form')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': AIZ.data.csrf
                },
                method: "POST",
                url: "{{ route('pos.set-shipping-address') }}",
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    // shipping address is set
                }
            });
        });

        function add_new_address() {
            var customer_id = $('#customer_id').val();
            $('#set_customer_id').val(customer_id);
            $('#new-address-modal').modal('show');
            $("#close-button").click();
        }

        function orderConfirmation() {
            $('#order-confirmation').html(`
            <div class="p-4 text-center">
                <i class="las la-spinner la-spin la-3x"></i>
            </div>
        `);
            $('#order-confirm').modal('show');
            $.post('{{ route('pos.getOrderSummary') }}', {
                _token: AIZ.data.csrf
            }, function(data) {
                $('#order-confirmation').html(data);
            });
        }

        function oflinePayment() {
            $('#offlin_payment').modal('show');
        }

        // Submit the final order
        function submitOrder(payment_type) {
            var user_id = $('select[name=user_id]').val();
            var shipping_address = $('input[name=address_id]:checked').val();
            var shipping = $('input[name=shipping]:checked').val();
            var discount = $('input[name=discount]').val();
            var offline_payment_method = $('input[name=offline_payment_method]').val();
            var offline_payment_amount = $('input[name=offline_payment_amount]').val();
            var offline_trx_id = $('input[name=trx_id]').val();
            var offline_payment_proof = $('input[name=payment_proof]').val();

            $.post('{{ route('pos.order_place') }}', {
                _token: AIZ.data.csrf,
                user_id: user_id,
                shipping_address: shipping_address,
                payment_type: payment_type,
                shipping: shipping,
                discount: discount,
                offline_payment_method: offline_payment_method,
                offline_payment_amount: offline_payment_amount,
                offline_trx_id: offline_trx_id,
                offline_payment_proof: offline_payment_proof
            }, function(data) {
                if (data.success == 1) {
                    AIZ.plugins.notify('success', data.message);
                    console.log(data);

                    // Display invoice in a modal
                    showInvoiceDialog(data.order);

                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            });
        }

        function showInvoiceDialog(order) {
            // Example data you might have:
            // order {
            //   id, date, grand_total,
            //   shipping_address: JSON string of {name, phone, etc.},
            //   order_details: [
            //       { product: { name }, quantity, price, total },
            //       ...
            //   ]
            // }
            // Adjust as needed based on your actual data structure.

            const shippingAddress = JSON.parse(order.shipping_address || '{}');
            const paidAmount = order.paid_amount ?? 0; // If you track how much is paid
            const changeAmount = (paidAmount - order.grand_total).toFixed(2);
            let totalAddons = 0;
            let totalOrder = 0;

            // For demonstration, we hardcode the invoice meta data:
            const tableNumber = 'LV-01';
            const timeString = '2:33:53 PM';
            const dateString = '2018-02-09';
            const receiptNumber = order.code;
            const storeIcon = "{{uploaded_asset(get_setting('pos_image'))}}"

            // Build the items table rows with variations and addons if available
            let itemsRows = "";
                order.order_details.forEach(item => {
                    // If your data has `item.name` instead of `item.product.name`, do:
                    itemsRows += `
                    <tr>
                       <td colspan="1">
                        <strong>${item.product.name}</strong>
                        ${item.variant ? `<br><small>${item.variant}</small>` : ""}
                        </td>
                        <td>${item.quantity}</td>
                        <td>${(item.price * item.quantity).toFixed(2)}</td>
                    </tr>
                    `;
                    totalOrder += item.price * item.quantity;

                    // Check if `addons` exists and is an array
                    // decode the JSON string to an array
                    const addons = item.addons != '' && typeof item.addons === 'string' ? JSON.parse(item.addons) : item.addons;
                    if (addons != null && Array.isArray(addons) && item.addons.length > 0) {
                    itemsRows += `
                        <tr>
                        <td colspan="3"><em>Addons:</em></td>
                        </tr>
                    `;
                    addons.forEach(addon => {
                        totalAddons += addon.price * addon.quantity;
                        itemsRows += `
                        <tr>
                            <td style="padding-left:20px;">${addon.name}</td>
                            <td>${addon.quantity}</td>
                            <td>${(addon.price * addon.quantity).toFixed(2)}</td>
                        </tr>
                        `;
                    });
                    }
                });

            // Build the complete invoice HTML
            const invoiceHtml = `
                <html>
                <head>
                    <style>
                        /* Styles for printing the invoice */
                        @media print {
                            @page {
                                size: 80mm auto;
                                margin: 0;
                            }
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            .no-print {
                                display: none !important;
                            }
                            html, body {
                                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                font-size: 14px;
                                line-height: 1.4;
                            }
                            .invoice-container {
                                padding: 10px;
                                margin: 0;
                                border: 1px dashed #000;
                            }
                        }
                        /* General invoice styles */
                        .invoice-header, .invoice-footer {
                            text-align: center;
                            margin-bottom: 10px;
                        }
                        .invoice-header h2 {
                            margin: 0 0 5px 0;
                            font-size: 18px;
                        }
                        .meta-table, .items-table, .totals-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                        }
                        .meta-table td, .items-table td, .items-table th, .totals-table td {
                            padding: 3px;
                            vertical-align: middle;
                        }
                        .meta-table td:first-child {
                            white-space: nowrap;
                        }
                        .items-table thead th {
                            border-bottom: 1px solid #000;
                            text-align: left;
                        }
                        .items-table td {
                            text-align: left;
                        }
                        .totals-table td {
                            text-align: right;
                        }
                        .totals-table td.label {
                            text-align: left;
                            width: 70%;
                        }
                        hr {
                            border: none;
                            border-bottom: 1px dotted #000;
                            margin: 5px 0;
                        }
                        .rtl {
                            direction: rtl;
                            text-align: right;
                        }
                    </style>
                </head>
                <body>
                <div class="invoice-container">
                    <!-- Header with Store Logo -->
                    <div class="invoice-header">
                        <img src="${storeIcon}" alt="Store Logo" style="max-width: 100%; height: auto;">
                    </div>
                    <!-- Meta Information -->
                    <table class="meta-table">
                        <tr>
                            <td>Time</td>
                            <td>${timeString}</td>
                            <td class="rtl">: وقت</td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td>${dateString}</td>
                            <td class="rtl">: تاريخ</td>
                        </tr>
                        <tr>
                            <td>Receipt #</td>
                            <td>${receiptNumber}</td>
                            <td class="rtl">: إيصال</td>
                        </tr>
                    </table>
                    <hr/>
                    <!-- Items Listing -->
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Item<br/><span class="rtl">اسم</span></th>
                                <th>Qty<br/><span class="rtl">كمية</span></th>
                                <th>Amount<br/><span class="rtl">السعر</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsRows}
                        </tbody>
                    </table>
                    <hr/>
                    <!-- Totals Section -->
                    <table class="totals-table">
                        <tr>
                            <td class="label">Amount:</td>
                            <td>${(totalOrder + totalAddons).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td class="label">Discount:</td>
                            <td>${(order.discount_total ?? 0).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td class="label">Total:</td>
                            <td>${((totalOrder + totalAddons) - order.coupon_discount).toFixed(2)}</td>
                        </tr>
                    </table>
                    <hr/>
                    <!-- Footer with a Thank You Message -->
                    <div class="invoice-footer">
                        <p>Thank you!<br/><span class="rtl">شكرا لك</span></p>
                    </div>
                </div>
                </body>
                </html>
            `;


            // Open a new window and write this HTML into it for printing
            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(invoiceHtml);
            printWindow.document.close();

            // Optional auto-print & close
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                //printWindow.close(); // uncomment if you want to close automatically
            };

            // refresh the page
            location.reload();
        }


        function printInvoice(orderId) {
            // $.get(`{{ route('pos.getInvoiceDetails') }}?order_id=${orderId}`, function(order) {
            //     if (!order) {
            //         AIZ.plugins.notify('danger', '{{ translate('Failed to fetch order details') }}');
            //         return;
            //     }

            // Epson ePOS Print Integration
            var ePosDev = new epson.ePOSDevice();
            ePosDev.connect('192.168.8.11', 443, function(resultConnect) {
                console.log('connect to printer:', resultConnect);
                if (resultConnect === 'SSL_CONNECT_OK') {
                    ePosDev.createDevice('local_printer', epson.ePOSDevice.TYPE_PRINTER, {
                        crypto: true,
                        buffer: false
                    }, function(deviceObj, errorCode) {
                        if (deviceObj === null) {
                            console.error('Failed to create device:', errorCode);
                            return;
                        }
                        console.log("Connection is success")

                        var printer = deviceObj;

                        // Print the invoice
                        printer.addTextAlign(printer.ALIGN_CENTER);
                        printer.addText(`Invoice\n`);
                        printer.addText(`Order ID: ${order.id}\n`);
                        printer.addText(`Date: ${order.date}\n\n`);
                        order.order_details.forEach((item) => {
                            printer.addText(
                                `${item.name} x${item.quantity} ${item.price} = ${item.total}\n`
                                );
                        });
                        printer.addText(`\nTotal: ${order.grand_total}\n`);
                        printer.addCut(printer.CUT_FEED);

                        // Send data to printer
                        printer.send();
                        // refresh the page
                        location.reload();
                    });
                } else {
                    console.error('Failed to connect to printer:', resultConnect);
                }
            });
            // });
        }

        // address helpers
        $(document).on('change', '[name=country_id]', function() {
            var country_id = $(this).val();
            get_states(country_id);
        });
        $(document).on('change', '[name=state_id]', function() {
            var state_id = $(this).val();
            get_city(state_id);
        });

        function get_states(country_id) {
            $('[name="state"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('get-state') }}",
                type: 'POST',
                data: {
                    country_id: country_id
                },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj != '') {
                        $('[name="state_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }

        function get_city(state_id) {
            $('[name="city"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('get-city') }}",
                type: 'POST',
                data: {
                    state_id: state_id
                },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj != '') {
                        $('[name="city_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }
        // Increase/decrease addon quantity
        function updateAddonQty(cartKey, addonId, delta) {
            // Get current quantity from the input
            let inputId = "#addon_qty_" + cartKey + "_" + addonId;
            let currentVal = parseInt($(inputId).val());
            let newVal = currentVal + delta;
            if (newVal < 0) newVal = 0;
            if (newVal <= 0) {
                removeAddon(cartKey, addonId);
                return;
            }

            // Post an AJAX request to update the addon quantity in the session
            $.post('{{ route('pos.updateAddon') }}', {
                _token: AIZ.data.csrf,
                cartKey: cartKey,
                addonId: addonId,
                quantity: newVal
            }, function(data) {
                if (data.success == 1) {
                    // Re-render the cart with updated data
                    $('#cart-details').html(data.view);
                    AIZ.extra.plusMinus();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            });
        }

        // Remove an addon entirely
        function removeAddon(cartKey, addonId) {
            $.post('{{ route('pos.removeAddon') }}', {
                _token: AIZ.data.csrf,
                cartKey: cartKey,
                addonId: addonId
            }, function(data) {
                if (data.success == 1) {
                    $('#cart-details').html(data.view);
                    AIZ.extra.plusMinus();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            });
        }
    </script>
    <script>
        $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
        if (options.url.startsWith("http://")) {
            options.url = options.url.replace("http://", "https://");
        }
    });

        const originalFetch = window.fetch;
        window.fetch = function (url, options) {
            if (typeof url === "string" && url.startsWith("http://")) {
                url = url.replace("http://", "https://");
            }
            return originalFetch(url, options);
        };
    </script>
@endsection
