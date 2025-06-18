{{-- <form class="form-default" role="form" action="{{ route('addresses.update', $address_data->id) }}" method="POST">
    @csrf
    <div class="p-3">
        <div class="row">
            <div class="col-md-2">
                <label>{{ translate('Address')}}</label>
            </div>
            <div class="col-md-10">
                <textarea class="form-control mb-3" placeholder="{{ translate('Your Address')}}" rows="2" name="address" required>{{ $address_data->address }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>{{ translate('Country')}}</label>
            </div>
            <div class="col-md-10">
                <div class="mb-3">
                    <select class="form-control aiz-selectpicker" data-live-search="true" data-placeholder="{{ translate('Select your country')}}" name="country_id" id="edit_country" required>
                        <option value="">{{ translate('Select your country') }}</option>
                        @foreach (\App\Models\Country::where('status', 1)->get() as $key => $country)
                        <option value="{{ $country->id }}" @if($address_data->country_id == $country->id) selected @endif>
                            {{ $country->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <label>{{ translate('State')}}</label>
            </div>
            <div class="col-md-10">
                <select class="form-control mb-3 aiz-selectpicker" name="state_id" id="edit_state"  data-live-search="true" required>
                    @foreach ($states as $key => $state)
                        <option value="{{ $state->id }}" @if($address_data->state_id == $state->id) selected @endif>
                            {{ $state->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <label>{{ translate('City')}}</label>
            </div>
            <div class="col-md-10">
                <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="city_id" required>
                    @foreach ($cities as $key => $city)
                        <option value="{{ $city->id }}" @if($address_data->city_id == $city->id) selected @endif>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if (get_setting('google_map') == 1)
            <div class="row">
                <input id="edit_searchInput" class="controls" type="text" placeholder="Enter a location">
                <div id="edit_map"></div>
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
                    <input type="text" class="form-control mb-3" id="edit_longitude" name="longitude" value="{{ $address_data->longitude }}" readonly="">
                </div>
            </div>
            <div class="row">
                <div class="col-md-2" id="">
                    <label for="exampleInputuname">Latitude</label>
                </div>
                <div class="col-md-10" id="">
                    <input type="text" class="form-control mb-3" id="edit_latitude" name="latitude" value="{{ $address_data->latitude }}" readonly="">
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-2">
                <label>{{ translate('Postal code')}}</label>
            </div>
            <div class="col-md-10">
                <input type="text" class="form-control mb-3" placeholder="{{ translate('Your Postal Code')}}" value="{{ $address_data->postal_code }}" name="postal_code" value="" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>{{ translate('Phone')}}</label>
            </div>
            <div class="col-md-10">
                <input type="text" class="form-control mb-3" placeholder="{{ translate('+880')}}" value="{{ $address_data->phone }}" name="phone" value="" required>
            </div>
        </div>
        <div class="form-group text-right">
            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
        </div>
    </div>
</form> --}}

<form class="form-default" role="form" action="{{ route('addresses.update', $address_data->id) }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="p-3">
            <div class="row row d-flex justify-content-center m-2">
                <input type="text" value="apartment" name="selected_type" id="selected_type_input" style="display: none;" value="{{ $address_data->selected_type }}">
                <div class="col-md-3 p-2 m-2 border rounded-2 w-2 d-flex justify-content-center" id="apartment">
                    <label class="m-0">{{ translate('Apartment')}}</label>
                </div>
                <div class="col-md-3 p-2 m-2 border rounded-2 w-2 d-flex justify-content-center" id="house">
                    <label class="m-0">{{ translate('House')}}</label>
                </div>
                <div class="col-md-3 p-2 m-2 border rounded-2 w-2 d-flex justify-content-center" id="office">
                    <label class="m-0">{{ translate('Office')}}</label>
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
                        value="{{ $address_data->building_name }}" >
                </div>
            </div>
            <!-- End House -->

            <div id="apartment_office_div">
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
                        value={{ $address_data->bloc }} required>
                </div>
                <!-- End Bloc -->
            </div>

            @if (get_setting('google_map') == 1)
                <div class="row">
                    <input id="edit_searchInput" class="controls" type="text" placeholder="Enter a location">
                    <div id="edit_map"></div>
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
                        <input type="text" class="form-control mb-3" id="edit_longitude" name="longitude" value="{{ $address_data->longitude }}" readonly="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2" id="">
                        <label for="exampleInputuname">Latitude</label>
                    </div>
                    <div class="col-md-10" id="">
                        <input type="text" class="form-control mb-3" id="edit_latitude" name="latitude" value="{{ $address_data->latitude }}" readonly="">
                    </div>
                </div>
            @endif

            <div style="height: 10px;"></div>
            <div class="row d-flex justify-content-between">
                <!-- Start Street -->
                <div class="col-md-6">
                    <div>
                        <label>{{ translate('Street') }}</label>
                    </div>
                    <input type="text" class="form-control mb-3" placeholder="" name="street"
                    value="{{ $address_data->street }}" required>
                </div>
                <!-- End Street -->
                <!-- Start Avenue -->
                <div class="col-md-6">
                    <div>
                        <label>{{ translate('Avenue') }} {{ translate('(optional)') }}</label>
                    </div>
                    <input type="text" class="form-control mb-3" placeholder="" name="avenue"
                    value="{{ $address_data->avenue }}">
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
                <!-- Start Building Number -->
                <div class="col-md-6">
                    <div>
                        <label id="building_number_label">{{ translate('Apt.Number') }}</label>
                    </div>
                    <input type="text" class="form-control mb-3" placeholder=""
                        name="building_number" value="{{ $address_data->building_number }}" required>
                </div>
                <!-- End Building Number -->
                <!-- Start Floor -->
                <div class="col-md-6">
                    <div>
                        <label>{{ translate('Floor') }}</label>
                    </div>
                    <input type="text" class="form-control mb-3" placeholder="" name="floor"
                    value="{{ $address_data->floor }}" required>
                </div>
                <!-- End Floor -->
                    <div class="col-md-11">
                        <div>
                            <label>{{ translate('Phone') }}</label>
                        </div>
                        <div id="input-wrapper">
                            <label for="number">+965</label>
                            <input type="number" class="form-control mb-3"  onchange="this.value = '+965' + this.value" id="number" placeholder="" name="phone"
                                   value="{{ $address_data->phone }}" required>
                        </div>
                    </div>
            </div>
            <div style="height: 35px;"></div>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
            </div>
        </div>
    </div>
</form>

{{-- @section('script')
    <script type="text/javascript">
    $(document).ready(function() {
            const selected_type = $("#selected_type_input").val();
            if(selected_type == "house") {
                $("#apartment_office_div").hide();
                $("#house_label").text("{{translate("House")}}");
                $("#apartment_office_div input").prop("required", false);
                $("#apartment").removeClass("type-selected");
                $("#house").addClass("type-selected");
                $("#office").removeClass("type-selected");
            }else if(selected_type == "apartment") {
                $("#apartment_office_div").show();
                $("#house_label").text("{{translate("Building Name")}}");
                $("#building_number_label").text("{{ translate('Apt.Number')}}");
                $("#apartment_office_div input").prop("required", true);
                $("#apartment").addClass("type-selected");
                $("#house").removeClass("type-selected");
                $("#office").removeClass("type-selected");
            }else if(selected_type == "office") {
                $("#apartment_office_div").show();
                $("#house_label").text("{{translate("Building Name")}}");
                $("#building_number_label").text("{{ translate('Company Number')}}");
                $("#apartment_office_div input").prop("required", true);
                $("#apartment").removeClass("type-selected");
                $("#house").removeClass("type-selected");
                $("#office").addClass("type-selected");
            }
    });
        function add_new_address(){
            get_states(117);
            $('#new-address-modal').modal('show');
        }

        function edit_address(address) {
            var url = '{{ route("addresses.edit", ":id") }}';
            url = url.replace(':id', address);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#edit_modal_body').html(response.html);
                    $('#edit-address-modal').modal('show');
                    AIZ.plugins.bootstrapSelect('refresh');

                    @if (get_setting('google_map') == 1)
                        var lat     = -33.8688;
                        var long    = 151.2195;

                        if(response.data.address_data.latitude && response.data.address_data.longitude) {
                            lat     = parseFloat(response.data.address_data.latitude);
                            long    = parseFloat(response.data.address_data.longitude);
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

        function get_states(country_id) {
            $('[name="state"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('get-state')}}",
                type: 'POST',
                data: {
                    country_id  : country_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="state_id"]').html(obj);
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
            if(selected_type == "house") {
                $("#apartment_office_div").hide();
                $("#house_label").text("{{translate("House")}}");
                $("#apartment_office_div input").prop("required", false);
            }else if(selected_type == "apartment") {
                $("#apartment_office_div").show();
                $("#house_label").text("{{translate("Building Name")}}");
                $("#building_number_label").text("{{ translate('Apt.Number')}}");
                $("#apartment_office_div input").prop("required", true);
            }else if(selected_type == "office") {
                $("#apartment_office_div").show();
                $("#house_label").text("{{translate("Building Name")}}");
                $("#building_number_label").text("{{ translate('Company Number')}}");
                $("#apartment_office_div input").prop("required", true);
            }
        });

        function get_city(state_id) {
            $('[name="city"]').html("");
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('get-city')}}",
                type: 'POST',
                data: {
                    state_id: state_id
                },
                success: function (response) {
                    var obj = JSON.parse(response);
                    if(obj != '') {
                        $('[name="city_id"]').html(obj);
                        AIZ.plugins.bootstrapSelect('refresh');
                    }
                }
            });
        }
    </script>


    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endsection --}}
@section('modal-style')
<style>

.rounded-2{
    border-radius: 15px; !important
}

.type-selected {
    background-color: var(--hov-primary)
}

</style>

@endsection
