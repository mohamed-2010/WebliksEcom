<style>
    .type-selected {
        background-color: #0063B2FF;
    }
</style>

<div>
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
                <div class="row">

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
</div>
