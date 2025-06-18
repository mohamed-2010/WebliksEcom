<style>
    .type-option {
      border: 1px solid #ccc;
      border-radius: 20px;
      padding: 10px;
      text-align: center;
      cursor: pointer;
      transition: background-color 0.3s, color 0.3s;
      margin-bottom: 10px;
    }
    .type-option.type-selected {
      background-color: #0063B2FF;
      color: #fff;
      border-color: #0063B2FF;
    }
  </style>

  <div class="container">
    <div class="row d-flex justify-content-center my-3">
      <input type="hidden" name="selected_type" id="selected_type_input" value="">
      <div class="col-md-4">
        <div class="type-option type-selected" id="apartment" onclick="selectType('apartment', this)">
          <label class="m-0">{{ translate('Apartment') }}</label>
        </div>
      </div>
      <div class="col-md-4">
        <div class="type-option" id="house" onclick="selectType('house', this)">
          <label class="m-0">{{ translate('House') }}</label>
        </div>
      </div>
      <div class="col-md-4">
        <div class="type-option" id="office" onclick="selectType('office', this)">
          <label class="m-0">{{ translate('Office') }}</label>
        </div>
      </div>
    </div>

    <!-- Static Inputs -->
    <div id="static-inputs">
      <div class="row">
        <div class="col-12">
          <label>{{ translate('Address Title (Optional)') }}</label>
          <input type="text" class="form-control mb-3" name="address_title" />
        </div>
      </div>
      <!-- Province (full width) -->
      <div class="row">
        <div class="col-12">
          <label>{{ translate('Province') }}</label>
          <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="province" id="province">
            <option value="">{{ translate('Select Province') }}</option>
          </select>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
            <label>{{ translate('City') }}</label>
            <select class="form-control mb-3" data-live-search="true" name="cities" id="cities">
                <option value="">{{ translate('Select City') }}</option>
            </select>
        </div>
    </div>

      <div class="row">
        <!-- Bloc Input -->
        <div class="col-md-6">
          <label>{{ translate('Bloc') }}</label>
          <input type="text" class="form-control mb-3" placeholder="" name="bloc" value="" required>
        </div>
        @if (get_setting('google_map') == 1)
        <!-- Google Map Section -->
        <div class="col-12">
          <input id="searchInput" class="controls mb-3" type="text" placeholder="{{ translate('Enter a location') }}">
          <div id="map"></div>
          <ul id="geoData" style="list-style: none; padding: 0;">
            <li style="display: none;">Full Address: <span id="location"></span></li>
            <li style="display: none;">Postal Code: <span id="postal_code"></span></li>
            <li style="display: none;">Country: <span id="country"></span></li>
            <li style="display: none;">Latitude: <span id="lat"></span></li>
            <li style="display: none;">Longitude: <span id="lon"></span></li>
          </ul>
        </div>
        <div class="col-md-2">
          <label>Longitude</label>
          <input type="text" class="form-control mb-3" id="longitude" name="longitude" readonly="">
        </div>
        <div class="col-md-2">
          <label>Latitude</label>
          <input type="text" class="form-control mb-3" id="latitude" name="latitude" readonly="">
        </div>
        @endif
      </div>

      <div class="row">
        <div class="col-md-6">
          <label>{{ translate('Street') }}</label>
          <input type="text" class="form-control mb-3" placeholder="" name="street" value="" required>
        </div>
        <div class="col-md-6">
          <label>{{ translate('Avenue') }} {{ translate('(optional)') }}</label>
          <input type="text" class="form-control mb-3" placeholder="" name="avenue" value="">
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <label>{{ translate('Phone') }}</label>
          <div class="input-group mb-3">
            <span class="input-group-text">+965</span>
            <input type="text" class="form-control" name="phone" placeholder="{{ translate('Phone') }}">
          </div>
        </div>
      </div>
    </div>

    <!-- Dynamic Inputs -->
    <div id="dynamic-inputs">
      <!-- Apartment Inputs -->
      <div id="apartment-inputs" style="display: none;">
        <div class="row">
          <div class="col-md-6">
            <label>{{ translate('Floor') }}</label>
            <input type="text" class="form-control mb-3" placeholder="" name="apartment_floor" value="" required>
          </div>
          <div class="col-md-6">
            <label>{{ translate('Building Number. or building name') }}</label>
            <input type="text" class="form-control mb-3" placeholder="" name="apartment_building" value="" required>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <label>{{ translate('Apt. Number') }}</label>
            <input type="text" class="form-control mb-3" placeholder="" name="apartment_apt_number" value="" required>
          </div>
        </div>
      </div>

      <!-- House Inputs -->
      <div id="house-inputs" style="display: none;">
        <div class="row">
          <div class="col-md-6">
            <label>{{ translate('Street') }}</label>
            <input type="text" class="form-control mb-3" name="street" />
          </div>
          <div class="col-md-6">
            <label>{{ translate('House Number') }}</label>
            <input type="text" class="form-control mb-3" name="house_number" />
          </div>
        </div>
      </div>

      <!-- Office Inputs -->
      <div id="office-inputs" style="display: none;">
        <div class="row">
          <div class="col-md-6">
            <label>{{ translate('Floor') }}</label>
            <input type="text" class="form-control mb-3" name="floor" />
          </div>
          <div class="col-md-6">
            <label>{{ translate('Building Number. or building name') }}</label>
            <input type="text" class="form-control mb-3" name="apartment_building" />
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <label>{{ translate('Office Number') }}</label>
            <input type="text" class="form-control mb-3" name="office_number" />
          </div>
          {{-- <div class="col-md-6">
            <label>{{ translate('Street') }}</label>
            <input type="text" class="form-control mb-3" name="street" />
          </div> --}}
        </div>
      </div>
    </div>
  </div>

  <script>
    window.onload = function() {
      selectType('apartment', document.getElementById('apartment'));
    };

    document.addEventListener("DOMContentLoaded", function() {
    selectType('apartment', document.getElementById('apartment'));
  });

    function selectType(type, element) {
      document.querySelectorAll('.type-option').forEach(el => el.classList.remove('type-selected'));
      element.classList.add('type-selected');

      document.getElementById('selected_type_input').value = type;

      document.getElementById('apartment-inputs').style.display = 'none';
      document.getElementById('house-inputs').style.display = 'none';
      document.getElementById('office-inputs').style.display = 'none';

      if (type === 'apartment') {
        document.getElementById('apartment-inputs').style.display = 'block';
      } else if (type === 'house') {
        document.getElementById('house-inputs').style.display = 'block';
      } else if (type === 'office') {
        document.getElementById('office-inputs').style.display = 'block';
      }
    }

    let country_id = 117;
    if (country_id) {
      $.ajax({
        url: "{{ route('get-state') }}",
        type: "POST",
        data: {
          _token: "{{ csrf_token() }}",
          country_id: country_id
        },
        success: function(response) {
          let options = '<option value="">{{ translate("Select Province") }}</option>';
          if (Array.isArray(response)) {
            response.forEach(function(item) {
              options += `<option value="${item}">${item}</option>`;
            });
          } else {
            let matches = response.match(/>([^<]+)</g);
            if (matches) {
              matches.forEach(function(match) {
                let value = match.replace(/>|</g, '').trim();
                if (value !== "Select State") {
                  options += `<option value="${value}">${value}</option>`;
                }
              });
            }
          }
          $('#province').html(options);
        },
        error: function(xhr) {
          console.error("Error fetching states:", xhr);
        }
      });
    } else {
      $('#province').html('<option value="">{{ translate("Select Province") }}</option>');
    }

    $(document).ready(function () {
    $('#province').on('change', function () {
        var province_id = $(this).val();
        if (province_id) {
            $.ajax({
                url: "{{ route('get-city') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    state_id: province_id
                },
                success: function(response) {
                    let cleanedResponse = response.replace(/\\/g, '');
                    $('#cities').html(cleanedResponse);
                    $('#cities').selectpicker('refresh');
                },
                error: function(xhr) {
                    console.error("Error fetching cities:", xhr);
                }
            });
        } else {
            $('#cities').html('<option value="">{{ translate("Select City") }}</option>');
            $('#cities').selectpicker('refresh');
        }
    });
});

</script>
