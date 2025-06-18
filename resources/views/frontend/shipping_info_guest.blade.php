@extends('frontend.layouts.app')

@section('content')
    <style>
        #input-wrapper * {
            position: absolute;
        }

        #input-wrapper label {
            z-index: 99;
            line-height: 40px!important;
            padding: 2px;
            margin-left: 5px;
        }

        #input-wrapper input {
            height: 25px;
            text-indent: 35px;
        }
    </style>
    <section class="pt-5 mb-4">
        <div class="container">
            <div class="row">
                @php
                    $delivery_info_step_active = get_setting('delivery_info_step_status');
                    $guest = App\Models\Guest::where('temp_user_id', Request::session()->get('temp_user_id'))->first();
                    $delivery_address = $guest != null ? App\Models\Address::where('guest_id', $guest->id)->first() : null;
                @endphp
                <div class="col-xl-8 mx-auto">
                    <div class="row aiz-steps arrow-divider">
                        <div class="col done">
                            <div class="text-center text-success">
                                <i class="la-3x mb-2 las la-shopping-cart"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ translate('1. My Cart') }}</h3>
                            </div>
                        </div>
                        <div class="col active">
                            <div class="text-center text-primary">
                                <i class="la-3x mb-2 las la-map"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block ">{{ translate('2. Shipping info') }}</h3>
                            </div>
                        </div>
                        @if ($delivery_info_step_active == true)
                            <div class="col">
                                <div class="text-center">
                                    <i class="la-3x mb-2 opacity-50 las la-truck"></i>
                                    <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">
                                        {{ translate('3. Delivery info') }}</h3>
                                </div>
                            </div>
                        @endif
                        <div class="col">
                            <div class="text-center">
                                <i class="la-3x mb-2 opacity-50 las la-credit-card"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">
                                    {{ $delivery_info_step_active == true ? translate('4. Payment') : translate('3. Payment') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-center">
                                <i class="la-3x mb-2 opacity-50 las la-check-circle"></i>
                                <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50 ">
                                    {{ $delivery_info_step_active == true ? translate('5. Confirmation') : translate('4. Confirmation') }}
                                </h3>
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
                    <form class="form-default" data-toggle="validator"
                        action="{{ route('checkout.store_delivery_info_guest') }}" role="form" method="POST" id="checkout-form">
                        @csrf
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
                                                        name="shipping_type"
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
                                                        name="shipping_type"
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
                                                        name="shipping_type"
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
                                                name="pickup_point_id"
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
                        <div id="pick_up_point_section" class="d-none">
                            @include('frontend.partials.pick_up_points')
                        </div>
                            <div class="bg-white p-4 rounded mb-4" id="delivery_info">

                                <div class="row row d-flex justify-content-center m-2">
                                    <input type="text" value="apartment" name="selected_type" id="selected_type_input"
                                        style="display: none;">
                                    <div class="col-md-3 p-2 m-2 border rounded-2 w-2 d-flex justify-content-center type-selected"
                                        id="apartment">
                                        <label class="m-0">{{ translate('Apartment') }}</label>
                                    </div>
                                    <div class="col-md-3 p-2 m-2 border rounded-2 w-2 d-flex justify-content-center"
                                        id="house">
                                        <label class="m-0">{{ translate('House') }}</label>
                                    </div>
                                    <div class="col-md-3 p-2 m-2 border rounded-2 w-2 d-flex justify-content-center"
                                        id="office">
                                        <label class="m-0">{{ translate('Office') }}</label>
                                    </div>
                                </div>


                                <!-- Start House -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <label id="house_label">{{ translate('Address Title') }}
                                            {{ translate('(Optional)') }}</label>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="text" class="form-control mb-3" placeholder="" name="building_name"
                                            value="{{ $delivery_address != null ? $delivery_address->building_name : '' }}">
                                    </div>
                                </div>
                                <!-- End House -->

                                <div>
                                    <div style="height: 10px;"></div>
                                    <div class="row d-flex justify-content-between">

                                        <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">{{ translate('State') }}</label>
                                            <select class="form-control mb-3 aiz-selectpicker" data-live-search="true"
                                                name="state_id" required>

                                            </select>
                                        </div>
                                    </div>
                                    </div>
                                    <div style="height: 10px;"></div>
                                </div>

                                {{-- <div class="form-group">
                                    <label class="control-label">{{ translate('Address')}}</label>
                                    <input type="text" class="form-control" name="address" placeholder="{{ translate('Address')}}" required value="{{$delivery_address != null ? $delivery_address->address : ''}}">
                                </div> --}}

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group has-feedback">
                                            <label class="control-label">{{ translate('City') }}</label>
                                            <select class="form-control mb-3 aiz-selectpicker" data-live-search="true"
                                                name="city_id" required>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ translate('Bloc') }}</label>
                                            <input type="text" class="form-control" name="bloc" required
                                                placeholder=""
                                                value="{{ $delivery_address != null ? $delivery_address->bloc : '' }}"
                                                required>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ translate('Street') }}</label>
                                            <input type="text" class="form-control" name="street" required
                                                placeholder=""
                                                value="{{ $delivery_address != null ? $delivery_address->street : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group has-feedback">
                                            <label class="control-label">{{ translate('Avenue') }}
                                                {{ translate('(Optional)') }}</label>
                                            <input type="text" class="form-control" name="avenue" placeholder=""
                                                value="{{ $delivery_address != null ? $delivery_address->avenue : '' }}">
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    {{-- <div class="col-md-6">
                                        <div class="form-group has-feedback" id="house_address">
                                            <label class="control-label"
                                                id="house_address_label">{{ translate('Apartment') }}</label>
                                            <input type="text" class="form-control" name="house" required
                                                placeholder=""
                                                value="{{ $delivery_address != null ? $delivery_address->house : '' }}">
                                        </div>
                                    </div> --}}
                                    <!-- Start building Number-->
                                    <div class="col-md-6" id="building_number_div">
                                          <div class="form-group has-feedback" >
                                        <div>
                                            <label id="building_number_label">{{ translate('building Number. or building name') }}</label>
                                        </div>
                                        <input type="text" class="form-control mb-3" placeholder=""
                                            name="building_number" id="building_number"
                                            value="{{ $delivery_address != null ? $delivery_address->building_number : '' }}" required>
                                            </div>
                                    </div>
                                    <!-- End building Number -->
                                    <!-- Start Apt Number -->
                                    <div class="col-md-6" id="apartment_office_div">
                                          <div class="form-group has-feedback" >
                                        <div>
                                            <label id="apt_number_label">{{ translate('Apt.Number') }}</label>
                                        </div>
                                        <input type="text" class="form-control mb-3" placeholder=""
                                            name="apt_number" id="apt_number"
                                            value="{{ $delivery_address != null ? $delivery_address->apt_number : '' }}" required>
                                            </div>
                                    </div>
                                    <!-- End Apt Number -->
                                    <!-- Start Floor -->
                                    <div class="col-md-6" id="floor_div">
                                         <div class="form-group has-feedback" >
                                        <div>
                                            <label>{{ translate('Floor') }}</label>
                                        </div>
                                        <input type="text" class="form-control mb-3" placeholder=""
                                            name="floor"
                                            value="{{ $delivery_address != null ? $delivery_address->floor : '' }}"
                                            required>
                                            </div>
                                    </div>
                                    <!-- End Floor -->
                                </div>

                                <input type="hidden" name="checkout_type" value="guest">
                            </div>
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ translate('Name') }}</label>
                                        <input type="text" class="form-control" name="name"
                                            placeholder="{{ translate('Name') }}" required
                                            value="{{ $guest != null ? $guest->name : '' }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ translate('Email') }}  {{ translate('(Optional)') }}</label>
                                        <input type="text" class="form-control" name="email"
                                            placeholder="{{ translate('Email') }}"
                                            value="{{ $guest != null ? $guest->email : '' }}">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group has-feedback">
                                        <label  class="control-label">{{ translate('Phone') }}</label>
                                        <div  id="input-wrapper">
                                            <label for="number">+965</label>
                                        <input style="
                                                     padding: 0.6rem 1rem;
                                                     font-size: 0.875rem;
                                                    height: calc(1.3125rem + 1.2rem + 2px);
                                                      border: 1px solid #e2e5ec;
                                                    color: #898b92;"
                                        type="number" lang="en" class="form-control"
                                            placeholder="0000000" name="phone" required id="phone_number_input"
                                            value="{{ $guest != null ? $guest->phone : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div class="row align-items-center p-4">
                            <div class="col-md-6 text-center text-md-left order-1 order-md-0">
                                <a href="{{ route('home') }}" class="btn btn-link">
                                    <i class="las la-arrow-left"></i>
                                    {{ translate('Return to shop') }}
                                </a>
                            </div>
                            <div class="col-md-6 text-center text-md-right">
                                <button type="submit"
                                    class="btn btn-primary fw-600">{{ translate('Continue to Delivery Info') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- state - city scripts -->
@section('script')
    <script>

            // Function to check if pickup point is selected
            function isPickupPointSelected() {
                return $('input[name^="shipping_type"]:checked').val() === 'pickup_point';
            }

            // Function to toggle required attributes
            function toggleRequiredFields(isPickupPoint) {
                const fieldsToToggle = [
                    'input[name="state_id"]',
                    'input[name="city_id"]',
                    'input[name="bloc"]',
                    'input[name="street"]',
                    'input[name="building_number"]',
                    'input[name="apt_number"]',
                    'input[name="floor"]',
                    'select[name^="state_id"]',
                    'select[name^="city_id"]',
                ];

                fieldsToToggle.forEach(field => {
                    $(field).prop('required', !isPickupPoint);
                });

                // Always keep these fields required
                $('input[name="phone"]').prop('required', true);
                $('input[name="name"]').prop('required', true);
            }

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
        $(document).ready(function() {
            AIZ.plugins.bootstrapSelect('refresh');
            // Toggle required fields on page load
                toggleRequiredFields(isPickupPointSelected());

            // Toggle required fields when shipping type changes
            $('input[name^="shipping_type"]').change(function() {
                toggleRequiredFields(isPickupPointSelected());
            });

            $('#checkout-form').submit(function(e) {
                // if (isPickupPointSelected()) {
                //     // remove any required attribute from the all fildes except phone number, email and name
                //     $('input').each(function() {
                //         if ($(this).attr('name') != 'phone' && $(this).attr('name') != 'email' && $(this).attr('name') != 'name') {
                //             console.log($(this).attr('name'));
                //             $(this).prop('required', false);
                //         }
                //     });
                //     $('select').each(function() {
                //         $(this).prop('required', false);
                //     });
                //     // If pickup point is selected, submit the form without additional checks
                //     $(this).unbind('submit').submit();
                //     return;
                // }
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

                // Get the phone number value
                var phoneNumber = $('#phone_number_input').val();

                // Check your condition here (e.g., if phone number is less than 1000000)
                if (phoneNumber.length < 7) { 
                    // Display an error message or take appropriate action
                    alert('Phone number must be at least 7 digits');
                } else {
                    // If the condition is satisfied, submit the form
                    $(this).unbind('submit').submit();
                }
            });
            add_new_address();
            const address = @JSON($delivery_address);
            if (address != null) {
                if (address['address_type'] == "house") {
                    $("#apartment_office_div").hide();
                    //$("#house_label").text("{{ translate('House') }} {{ translate('(Optional)') }}");
                    $("#house_address").hide();
                    $("#apt_number").prop("required", false);
                    $("#apartment").removeClass("type-selected");
                    $("#house").addClass("type-selected");
                    $("#office").removeClass("type-selected");
                    $("#building_number_label").text("{{ translate('House Number') }}");
                    $("#floor_div").hide();
                    
                } else if (address['address_type'] == "apartment") {
                    $("#apartment_office_div").show();
                    //$("#house_label").text("{{ translate('Building Name') }} {{ translate('(Optional)') }}");
                    $("#apt_number_label").text("{{ translate('Apt.Number') }}");
                    // $("#house_address_label").text("{{ translate('Appartment') }}");
                    $("#apt_number").prop("required", true);
                    $("#apartment").addClass("type-selected");
                    $("#house").removeClass("type-selected");
                    $("#office").removeClass("type-selected");
                    $("#house_address").show();
                } else if (address['address_type'] == "office") {
                    $("#apartment_office_div").show();
                    //$("#house_label").text("{{ translate('Building Name') }} {{ translate('(Optional)') }}");
                    $("#apt_number_label").text("{{ translate('Company Number') }}");
                    $("#apt_number").prop("required", true);
                    $("#apartment").removeClass("type-selected");
                    $("#house").removeClass("type-selected");
                    $("#office").addClass("type-selected");
                    $("#house_address").show();
                }
            }
        });
    </script>
    <script type="text/javascript">
        function add_new_address() {
            get_states(117);
        }

        function edit_address(address) {
            var url = '{{ route('addresses.edit', ':id') }}';
            url = url.replace(':id', address);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#edit_modal_body').html(response.html);
                    $('#edit-address-modal').modal('show');
                    AIZ.plugins.bootstrapSelect('refresh');

                    @if (get_setting('google_map') == 1)
                        var lat = -33.8688;
                        var long = 151.2195;

                        if (response.data.address_data.latitude && response.data.address_data.longitude) {
                            lat = parseFloat(response.data.address_data.latitude);
                            long = parseFloat(response.data.address_data.longitude);
                        }

                        initialize(lat, long, 'edit_');
                    @endif
                }
            });
        }

        $(document).on('change', '[name=country_id]', function() {
            var country_id = $(this).val();
            get_states(country_id);
        });

        $(document).on('change', '[name=state_id]', function() {
            var state_id = $(this).val();
            get_city(state_id);
        });

        $(document).on('click', '#apartment', function() {
            $("#apartment").addClass("type-selected");
            $("#house").removeClass("type-selected");
            $("#office").removeClass("type-selected");
            $("#selected_type_input").val("apartment").trigger('change');
        });

        $(document).on('click', '#house', function() {
            $("#apartment").removeClass("type-selected");
            $("#house").addClass("type-selected");
            $("#office").removeClass("type-selected");
            $("#selected_type_input").val("house").trigger('change');
        });


        $(document).on('click', '#office', function() {
            $("#apartment").removeClass("type-selected");
            $("#house").removeClass("type-selected");
            $("#office").addClass("type-selected");
            $("#selected_type_input").val("office").trigger('change');
        });

        $(document).on('change', '#selected_type_input', function() {
            const selected_type = $("#selected_type_input").val();
            if (selected_type == "house") {
                $("#apartment_office_div").hide();
                $("#floor_div").hide();
                //$("#house_label").text("{{ translate('House') }} {{ translate('(Optional)') }}");
                $("#building_number_label").text("{{ translate('House Number') }}");
                $("#apartment_office_div input").prop("required", false);
                $("#house_address").hide();
            } else if (selected_type == "apartment") {
                $("#apartment_office_div").show();
                $("#floor_div").show();
                 $("#building_number_label").text("{{ translate('building Number. or building name') }}");
                //$("#house_label").text("{{ translate('Building Name') }} {{ translate('(Optional)') }}");
                $("#apt_number_label").text("{{ translate('Apt.Number') }}");
                
                $("#apartment_office_div input").prop("required", true);
                $("#house_address").show();
                
            } else if (selected_type == "office") {
                $("#apartment_office_div").show();
                $("#floor_div").show();
                //$("#house_label").text("{{ translate('Building Name') }} {{ translate('(Optional)') }}");
                $("#building_number_label").text("{{ translate('building Number. or building name') }}");
                $("#apt_number_label").text("{{ translate('Company Number') }}");
                $("#apartment_office_div input").prop("required", true);
                $("#house_address").show();
            }
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
                        if (obj.includes('selected')) {
                            // If 'selected' is present in obj, get the city for this state
                            var selectedStateId = $('[name="state_id"]').val();
                            get_city(selectedStateId);
                        }
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
    </script>
@endsection
<!-- state - city scripts -->


@endsection

@section('modal')
@include('frontend.partials.address_modal')
@endsection
