<div class="card-header mb-2">
    <h5 class="mb-0 h6">{{translate('Add Your Brand Base Coupon')}}</h5>
</div>
<div class="form-group row">
    <label class="col-lg-3 control-label" for="code">{{translate('Coupon code')}}</label>
    <div class="col-lg-9">
        <input type="text" placeholder="{{translate('Coupon code')}}" id="code" name="code" value="{{ $coupon->code }}" class="form-control" required>
    </div>
</div>
<div class="form-group row">
    <label class="col-lg-3 col-from-label">{{translate('Minimum Shopping')}}</label>
    <div class="col-lg-9">
       <input type="number" lang="en" min="0" step="0.01" name="min_buy" class="form-control" value="{{ json_decode($coupon->details)[0]->min_buy }}" required>
    </div>
  </div>
<div class="product-choose-list">
    <div class="product-choose">
        <div class="form-group row">
            <label class="col-lg-3 control-label" for="name">{{translate('Brand')}}</label>
            <div class="col-lg-9">
                <select name="brand_ids[]" class="form-control product_id aiz-selectpicker" data-live-search="true" data-selected-text-format="count" required multiple id="brand_ids">
                    @foreach($brands as $key => $brand)
                        <option value="{{$brand->id}}"
                            @foreach (json_decode($coupon->details) as $key => $details)
                                @if ($details->brand_id == $brand->id)
                                    selected
                                @endif
                            @endforeach
                            >{{$brand->getTranslation('name')}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<!-- Excluded Products Selection -->
<div class="form-group row">
    <label class="col-lg-3 col-form-label">{{ translate('Excluded Products') }}</label>
    <div class="col-lg-9">
        <select name="excluded_product_ids[]" id="excluded_product_ids" class="form-control aiz-selectpicker" data-live-search="true" multiple>
            <!-- Product options will be dynamically populated here -->
        </select>
    </div>
</div>
@php
  $start_date = date('m/d/Y', $coupon->start_date);
  $end_date = date('m/d/Y', $coupon->end_date);
@endphp
<div class="form-group row">
    <label class="col-sm-3 control-label" for="start_date">{{translate('Date')}}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control aiz-date-range" value="{{ $start_date .' - '. $end_date }}" name="date_range" placeholder="{{ translate('Select Date') }}">
    </div>
</div>

<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{translate('Discount')}}</label>
   <div class="col-lg-5">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Discount')}}" value="{{ $coupon->discount }}" name="discount" class="form-control" required>

   </div>
   <div class="col-lg-4">
       <select class="form-control aiz-selectpicker" name="discount_type">
           <option value="amount" @if ($coupon->discount_type == 'amount') selected  @endif>{{translate('Amount')}}</option>
           <option value="percent" @if ($coupon->discount_type == 'percent') selected  @endif>{{translate('Percent')}}</option>
       </select>
   </div>
</div>

@php
    $excludedProductIds = [];
    foreach (json_decode($coupon->details) as $details) {
        if (isset($details->excluded_product_ids) && is_array($details->excluded_product_ids)) {
            $excludedProductIds = array_merge($excludedProductIds, $details->excluded_product_ids);
        }
    }
@endphp

<script type="text/javascript">

    $(document).ready(function(){
        $('.aiz-date-range').daterangepicker();
        AIZ.plugins.bootstrapSelect('refresh');
    });

</script>

<script type="text/javascript">
    $(document).ready(function() {
        var excludedProductIds = @json($excludedProductIds);

        $('#category_ids, #brand_ids').on('change', function() {
            // Get selected categories and brands
            // var categoryIds = $('#category_ids').val();
            // var brandIds = $('#brand_ids').val();

            // Update the excluded products dropdown
            updateExcludedProductIds();
        });

        function updateExcludedProductIds() {
            // Send AJAX request to get products based on selected categories and brands
            // Get selected categories and brands
            var categoryIds = $('#category_ids').val();
            var brandIds = $('#brand_ids').val();
            $.ajax({
                url: '{{ route("get_products_by_category_brand") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_ids: categoryIds,
                    brand_ids: brandIds
                },
                success: function(response) {
                    // Update the excluded products dropdown
                    $('#excluded_product_ids').empty();
                    $.each(response.products, function(key, product) {
                        // Mark the product as selected if it's in the excludedProductIds array
                        // convert id to string

                        var isSelected = excludedProductIds.includes(product.id.toString()) ? 'selected' : '';
                        $('#excluded_product_ids').append('<option value="' + product.id + '" ' + isSelected + '>' + product.name + '</option>');
                    });
                    $('#excluded_product_ids').selectpicker('refresh');
                }
            });
        }

        // Populate the excluded products dropdown with the previously selected products
        updateExcludedProductIds();
    });
</script>
