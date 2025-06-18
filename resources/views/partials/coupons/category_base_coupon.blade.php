<div class="card-header mb-2">
    <h3 class="h6">{{translate('Add Your Category Base Coupon')}}</h3>
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
            <label class="col-lg-3 col-from-label" for="name">{{translate('Category')}}</label>
            <div class="col-lg-9">
                <select name="category_ids[]" class="form-control category_id aiz-selectpicker" data-live-search="true" data-selected-text-format="count" required multiple id="category_ids">
                    @foreach($categories as $category)
                        <option value="{{$category->id}}">{{ $category->getTranslation('name') }}</option>
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

    $(document).ready(function(){
        $('.aiz-date-range').daterangepicker();
        AIZ.plugins.bootstrapSelect('refresh');
    });

</script>

<script type="text/javascript">
$(document).ready(function() {
    var categories = @json($categories);

    $('#category_ids').on('change', function() {
        var selectedCategories = $('#category_ids').val() || [];
        var updatedCategories = [];

        // إضافة الأقسام الرئيسية والأقسام الفرعية فقط إذا كانت الشروط صحيحة
        $.each(categories, function(key, category) {
            // إضافة القسم إذا كان القسم الرئيسي محددًا أو القسم الفرعي محددًا يدويًا
            if (selectedCategories.includes(category.id.toString())) {
                updatedCategories.push(category.id.toString());

                // إضافة الأقسام الفرعية إذا تم تحديد القسم الرئيسي
                if (category.parent_id == 0) {
                    $.each(categories, function(subKey, subCategory) {
                        if (subCategory.parent_id == category.id) {
                            if (!updatedCategories.includes(subCategory.id.toString())) {
                                updatedCategories.push(subCategory.id.toString());
                            }
                        }
                    });
                }
            }
        });

        $('#category_ids').val([...new Set(updatedCategories)]);
        $('#category_ids').selectpicker('refresh');
    });

    // تحديث قائمة المنتجات المستبعدة بناءً على التغيير
    $('#category_ids, #brand_ids').on('change', function() {
        var categoryIds = $('#category_ids').val() || [];
        var brandIds = $('#brand_ids').val() || [];

        $.ajax({
            url: '{{ route("get_products_by_category_brand") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                category_ids: categoryIds,
                brand_ids: brandIds
            },
            success: function(response) {
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