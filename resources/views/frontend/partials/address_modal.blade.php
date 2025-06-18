<style>
    #input-wrapper * {
        position: absolute;
    }

    #input-wrapper label {
        z-index: 99;
        line-height: 38px;
        padding: 2px;
        margin-left: 5px;
    }

    #input-wrapper input {
        height: 40px;
        text-indent: 35px;
    }
</style>

<div class="modal fade" id="new-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('New Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="form-default" role="form" action="{{ route('addresses.store') }}" method="POST"
                id="address_form">
                @csrf
                <div class="modal-body">
                    <div class="p-3">
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
                            <div class="col-md-2">
                                <label id="house_label">{{ translate('Address Title') }}
                                    {{ translate('(Optional)') }}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="" name="building_name"
                                    value="">
                            </div>
                        </div>
                        <!-- End House -->

                        <div >
                            <div style="height: 10px;"></div>
                            <div class="row d-flex justify-content-between">
                                <div class="col-md-12">
                                    <div>
                                        <label>{{ translate('State') }}</label>
                                    </div>
                                    <select class="form-control mb-3 aiz-selectpicker" data-live-search="true"
                                        name="state_id" required>

                                    </select>
                                </div>
                            </div>
                            <div style="height: 10px;"></div>
                        </div>

                        {{-- <div class="row" hidden>
                            <div class="col-md-2">
                                <label>{{ translate('Country')}}</label>
                            </div>
                            <div class="col-md-10">
                                <div class="mb-3">
                                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your country') }}" name="country_id" required>
                                        <option value="">{{ translate('Select your country') }}</option>
                                        @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div> --}}


                        <div class="row d-flex justify-content-between">
                            <div class="col-md-6">
                                <div>
                                    <label>{{ translate('City') }}</label>
                                </div>
                                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true"
                                    name="city_id" required>

                                </select>
                            </div>
                            <!-- Start Bloc -->
                            <div class="col-md-6">
                                <div>
                                    <label>{{ translate('Bloc') }}</label>
                                </div>
                                <input type="text" class="form-control mb-3" placeholder="" name="bloc"
                                    value="" required>
                            </div>
                            <!-- End Bloc -->
                        </div>

                        @if (get_setting('google_map') == 1)
                            <div class="row">
                                <input id="searchInput" class="controls" type="text"
                                    placeholder="{{ translate('Enter a location') }}">
                                <div id="map"></div>
                                <ul id="geoData">
                                    <li style="display: none;">Full Address: <span id="location"></span></li>
                                    <li style="display: none;">Postal Code: <span id="postal_code"></span></li>
                                    <li style="display: none;">Country: <span id="country"></span></li>
                                    <li style="display: none;">Latitude: <span id="lat"></span></li>
                                    <li style="display: none;">Longitude: <span id="lon"></span></li>
                                </ul>
                            </div>

                            <div class="row">
                                <div class="col-md-2" id="">
                                    <label for="exampleInputuname">Longitude</label>
                                </div>
                                <div class="col-md-10" id="">
                                    <input type="text" class="form-control mb-3" id="longitude" name="longitude"
                                        readonly="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2" id="">
                                    <label for="exampleInputuname">Latitude</label>
                                </div>
                                <div class="col-md-10" id="">
                                    <input type="text" class="form-control mb-3" id="latitude" name="latitude"
                                        readonly="">
                                </div>
                            </div>
                        @endif

                        {{-- <div class="row" hidden>
                            <div class="col-md-2">
                                <label>{{ translate('Postal code')}}</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('Your Postal Code')}}" name="postal_code" value="" required>
                            </div>
                        </div> --}}



                        <div style="height: 10px;"></div>
                        <div class="row d-flex justify-content-between">
                            <!-- Start Street -->
                            <div class="col-md-6">
                                <div>
                                    <label>{{ translate('Street') }}</label>
                                </div>
                                <input type="text" class="form-control mb-3" placeholder="" name="street"
                                    value="" required>
                            </div>
                            <!-- End Street -->
                            <!-- Start Avenue -->
                            <div class="col-md-6">
                                <div>
                                    <label>{{ translate('Avenue') }} {{ translate('(optional)') }}</label>
                                </div>
                                <input type="text" class="form-control mb-3" placeholder="" name="avenue"
                                    value="">
                            </div>
                            <!-- End Avenue -->
                        </div>
                        <div style="height: 10px;"></div>

                        <div class="row d-flex justify-content-between">
                            {{-- <div class="col-md-6">
                                <div class="form-group has-feedback" id="house_address">
                                    <label class="control-label"
                                        id="house_address_label">{{ translate('Apartment') }}</label>
                                    <input type="text" class="form-control" name="house" required
                                        placeholder="" value="">
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
                                            value="" required>
                                            </div>
                                    </div>
                                    <!-- End building Number -->
                                    <!-- Start Apt Number -->
                                    <div class="col-md-6" id="apt_div">
                                          <div class="form-group has-feedback" >
                                        <div>
                                            <label id="apt_number_label">{{ translate('Apt.Number') }}</label>
                                        </div>
                                        <input type="text" class="form-control mb-3" placeholder=""
                                            name="apt_number" id="apt_number"
                                            value="" required>
                                            </div>
                                    </div>
                                    <!-- End Apt Number -->
                            <!-- Start Floor -->
                            <div class="col-md-6" id="floor_div">
                                <div>
                                    <label>{{ translate('Floor') }}</label>
                                </div>
                                <input type="text" class="form-control mb-3" placeholder="" name="floor"
                                    value="" required>
                            </div>
                            <!-- End Floor -->

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
                                                value="">
                                            </div>
                                        </div>
                                    </div>

                        </div>
                        <div style="height: 35px;     margin-bottom: 15px;"></div>



                        {{-- <div class="row">
                            <div class="col-md-2">
                                <label>{{ translate('Additional directions (optional)') }}</label>
                            </div>
                            <div class="col-md-10">
                                <textarea class="form-control mb-3" placeholder="{{ translate('Your Additional Directions') }}" rows="2"
                                    name="address"></textarea>
                            </div>
                        </div> --}}
                        {{-- <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ translate('Name') }}</label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ translate('Name') }}" required
                                        value="{{ '' }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ translate('Email') }}  {{ translate('(Optional)') }}</label>
                                    <input type="text" class="form-control" name="email"
                                        placeholder="{{ translate('Email') }}"
                                        value="{{ '' }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group has-feedback">
                                    <label class="control-label">{{ translate('Phone') }}</label>
                                    <input type="number" lang="en" class="form-control"
                                        placeholder="0000000" name="phone" required id="phone_number_input"
                                        value="{{ '' }}">
                                </div>
                            </div>
                        </div> --}}
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-address-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ translate('Edit Address') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="edit_modal_body">

            </div>
        </div>
    </div>
</div>

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#address_form.form-default').submit(function(e) {
                e.preventDefault(); // Prevent the form from submitting initially
                $(this).unbind('submit').submit();
            });
        });

        function add_new_address() {
            get_states(117);
            $('#new-address-modal').modal('show');
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
                    const address = response.data['address_data'];
                    $(document).on('click', '#edit_modal_body #apartment', function() {
                        $("#edit_modal_body #apartment").addClass("type-selected");
                        $("#edit_modal_body #house").removeClass("type-selected");
                        $("#edit_modal_body #office").removeClass("type-selected");
                        $("#edit_modal_body #selected_type_input").val("apartment").trigger('change');
                    });


                    $(document).on('click', '#edit_modal_body #house', function() {
                        $("#edit_modal_body #apartment").removeClass("type-selected");
                        $("#edit_modal_body #house").addClass("type-selected");
                        $("#edit_modal_body #office").removeClass("type-selected");
                        $("#edit_modal_body #selected_type_input").val("house").trigger('change');
                    });


                    $(document).on('click', '#edit_modal_body #office', function() {
                        $("#edit_modal_body #apartment").removeClass("type-selected");
                        $("#edit_modal_body #house").removeClass("type-selected");
                        $("#edit_modal_body #office").addClass("type-selected");
                        $("#edit_modal_body #selected_type_input").val("office").trigger('change');
                    });
                    $(document).on('change', '#edit_modal_body #selected_type_input', function() {
                        const selected_type = $("#edit_modal_body #selected_type_input").val();
                        if (selected_type == "house") {
                            $("#edit_modal_body #house_address").hide();
                        } else if (selected_type == "apartment") {
                            $("#edit_modal_body #apt_number_label").text("{{ translate('Apt.Number') }}");
                            $("#edit_modal_body #house_address").show();
                        } else if (selected_type == "office") {
                            $("#edit_modal_body #apt_number_label").text("{{ translate('Company Number') }}");
                            $("#edit_modal_body #house_address").show();
                        }
                    });
                    if (address != null) {
                        if (address['address_type'] == "house") {
                            $("#edit_modal_body #house_address").hide();
                            $("#edit_modal_body #apartment").removeClass("type-selected");
                            $("#edit_modal_body #house").addClass("type-selected");
                            $("#edit_modal_body #office").removeClass("type-selected");
                            $("#edit_modal_body #floor_div").hide();
                            $("#edit_modal_body #apt_div").hide();
                        } else if (address['address_type'] == "apartment") {
                            $("#edit_modal_body #apt_number_label").text("{{ translate('Apt.Number') }}");
                            // $("#house_address_label").text("{{ translate('Appartment') }}");
                            $("#edit_modal_body #apartment").addClass("type-selected");
                            $("#edit_modal_body #house").removeClass("type-selected");
                            $("#edit_modal_body #office").removeClass("type-selected");
                            $("#edit_modal_body #house_address").show();
                            $("#edit_modal_body #floor_div").show();
                            $("#edit_modal_body #apt_div").show();

                        } else if (address['address_type'] == "office") {
                            $("#edit_modal_body #apt_number_label").text("{{ translate('Company Number') }}");
                            $("#edit_modal_body #apartment").removeClass("type-selected");
                            $("#edit_modal_body #house").removeClass("type-selected");
                            $("#edit_modal_body #office").addClass("type-selected");
                            $("#edit_modal_body #house_address").show();
                            $("#edit_modal_body #floor_div").show();
                            $("#edit_modal_body #apt_div").show();
                        }
                    }

                    @if (get_setting('google_map') == 1)
                        var lat = -33.8688;
                        var long = 151.2195;

                        if (response.data.address_data.latitude && response.data.address_data.longitude) {
                            lat = parseFloat(response.data.address_data.latitude);
                            long = parseFloat(response.data.address_data.longitude);
                        }

                        initialize(lat, long, 'edit_');
                    @endif
                    get_edit_address_states(117, address);
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

        function get_edit_address_states(country_id, address_id) {
            $('[name="state"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('get-state') }}",
                type: 'POST',
                data: {
                    country_id: country_id,
                    address_id: address_id
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

        function get_edit_address_city(state_id, address_id) {
            $('[name="city"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('get-city') }}",
                type: 'POST',
                data: {
                    state_id: state_id,
                    address_id: address_id
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
                $("#house_address").hide();
                $("#building_number_label").text("{{ translate('House Number') }}");
                $("#floor_div").hide();
                $("#apt_div").hide();
            } else if (selected_type == "apartment") {
                $("#building_number_label").text("{{ translate('building Number. or building name') }}");
                $("#apt_number_label").text("{{ translate('Apt.Number') }}");
                $("#house_address").show();
                 $("#floor_div").show();
                $("#apt_div").show();
            } else if (selected_type == "office") {
                $("#building_number_label").text("{{ translate('building Number. or building name') }}");
                $("#apt_number_label").text("{{ translate('Company Number') }}");
                $("#house_address").show();
                $("#floor_div").show();
                $("#apt_div").show();
            }
        });

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

<script type="text/javascript">

    $('.new-email-verification').on('click', function() {
        $(this).find('.loading').removeClass('d-none');
        $(this).find('.default').addClass('d-none');
        var email = $("input[name=email]").val();

        $.post('{{ route('user.new.verify') }}', {_token:'{{ csrf_token() }}', email: email}, function(data){
            data = JSON.parse(data);
            $('.default').removeClass('d-none');
            $('.loading').addClass('d-none');
            if(data.status == 2)
                AIZ.plugins.notify('warning', data.message);
            else if(data.status == 1)
                AIZ.plugins.notify('success', data.message);
            else
                AIZ.plugins.notify('danger', data.message);
        });
    });
</script>

<script>
    function checkPasswordStrength() {
        let password = document.getElementById('new_password').value;
        document.getElementById('min-length').classList.toggle('text-success', password.length >= 8);
        document.getElementById('min-length').classList.toggle('text-danger', password.length < 8);
        document.getElementById('uppercase').classList.toggle('text-success', /[A-Z]/.test(password));
        document.getElementById('uppercase').classList.toggle('text-danger', !/[A-Z]/.test(password));
        document.getElementById('lowercase').classList.toggle('text-success', /[a-z]/.test(password));
        document.getElementById('lowercase').classList.toggle('text-danger', !/[a-z]/.test(password));
        document.getElementById('number').classList.toggle('text-success', /[0-9]/.test(password));
        document.getElementById('number').classList.toggle('text-danger', !/[0-9]/.test(password));
    }

    function validateConfirmPassword() {
        let password = document.getElementById('new_password').value;
        let confirmPassword = document.getElementById('confirm_password').value;
        document.getElementById('match-password').classList.toggle('text-success', password === confirmPassword);
        document.getElementById('match-password').classList.toggle('text-danger', password !== confirmPassword);
    }

    function validatePassword() {
        let password = document.getElementById('new_password').value;
        let confirmPassword = document.getElementById('confirm_password').value;
        let valid = password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password) && password === confirmPassword;
        if (!valid) {
            alert('Please make sure your password meets all requirements.');
        }
        return valid;
    }
</script>


    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endsection

@section('modal-style')
    <style>
        .rounded-2 {
            border-radius: 15px;
            !important
        }

        .type-selected {
            background-color: var(--hov-primary)
        }
    </style>
@endsection
