<div class="card-header mb-2">
    <h3 class="h6">{{translate('Add Your Brand Base Coupon')}}</h3>
</div>
<div class="form-group row">
    <label class="col-lg-3 col-from-label" for="code">{{translate('Coupon code')}}</label>
    <div class="col-lg-9">
        <input type="text" placeholder="{{translate('Coupon code')}}" id="code" name="code" class="form-control" required>
    </div>
</div>
<div class="form-group row">
    <label class="col-lg-3 col-from-label">{{translate('Minimum Shopping')}}</label>
    <div class="col-lg-9">
       <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Minimum Shopping')}}" name="min_buy" class="form-control" required>
    </div>
 </div>
<div class="product-choose-list">
    <div class="product-choose">
        <div class="form-group row">
            <label class="col-lg-3 col-from-label" for="name">{{translate('Brand')}}</label>
            <div class="col-lg-9">
                <select name="brand_ids[]" class="form-control brand_id aiz-selectpicker" data-live-search="true" data-selected-text-format="count" required multiple id="brand_ids">
                    @foreach($brands as $brand)
                        <option value="{{$brand->id}}">{{ $brand->getTranslation('name') }}</option>
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
<br>
<div class="form-group row">
    <label class="col-sm-3 control-label" for="start_date">{{translate('Date')}}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="{{ translate('Select Date') }}">
    </div>
</div>
<div class="form-group row">
   <label class="col-lg-3 col-from-label">{{translate('Discount')}}</label>
   <div class="col-lg-5">
      <input type="number" lang="en" min="0" step="0.01" placeholder="{{translate('Discount')}}" name="discount" class="form-control" required>
   </div>
   <div class="col-lg-4">
       <select class="form-control aiz-selectpicker" name="discount_type">
           <option value="amount">{{translate('Amount')}}</option>
           <option value="percent">{{translate('Percent')}}</option>
       </select>
   </div>
</div>


<script type="text/javascript">

    $(document).ready(function(){
        $('.aiz-date-range').daterangepicker();
        AIZ.plugins.bootstrapSelect('refresh');
    });

</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#category_ids, #brand_ids').on('change', function() {
            // Get selected categories and brands
            var categoryIds = $('#category_ids').val();
            var brandIds = $('#brand_ids').val();

            // Send AJAX request to get products based on selected categories and brands
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
                        $('#excluded_product_ids').append('<option value="' + product.id + '">' + product.name + '</option>');
                    });
                    $('#excluded_product_ids').selectpicker('refresh');
                }
            });
        });
    });
</script>
