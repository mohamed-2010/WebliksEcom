@extends('backend.layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Product Bulk Export')}}</h5>
        </div>
        <div class="card-body">
            <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                <strong>{{ translate('Step 1')}}:</strong>
                <p>1. {{translate('Select the export type (All, Category, or Brand)')}}.</p>
                <p>2. {{translate('If you select Category, choose a category and optionally a subcategory')}}.</p>
                <p>3. {{translate('If you select Brand, choose a brand')}}.</p>
                <p>4. {{translate('Click "Export CSV" to download the filtered products')}}.</p>
            </div>
            <br>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6"><strong>{{translate('Export Product File')}}</strong></h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('product_bulk_export.export') }}" method="GET">
                @csrf
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">{{ translate('Exclude Out of Stock') }}</label>
                    <div class="col-sm-3">
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input type="checkbox" onchange="updateSettings(this, 'exclude_outofstock')" @if(get_setting('exclude_outofstock') == '1') checked @endif name="exclude_outofstock" id="exclude_outofstock">
                            <span></span>
                        </label>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">{{translate('Export Type')}}</label>
                    <div class="col-sm-3">
                        <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="export_type" id="export_type" required>
                            <option value="all">{{translate('All Products')}}</option>
                            {{-- <option value="all_without_outofstock">{{translate('All Products Without Out of Stock')}}</option> --}}
                            <option value="category">{{translate('By Category')}}</option>
                            <option value="brand">{{translate('By Brand')}}</option>
                        </select>
                    </div>
                </div>

                <!-- Category Selector (Hidden by Default) -->
                <div class="form-group row" id="category_selector" style="display: none;">
                    <label class="col-sm-3 col-from-label">{{translate('Category')}}</label>
                    <div class="col-sm-3">
                        <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="category_id[]" id="category_id" multiple>
                            <option value="">{{translate('Select Category')}}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Subcategory Selector (Hidden by Default) -->
                <div class="form-group row" id="subcategory_selector" style="display: none;">
                    <label class="col-sm-3 col-from-label">{{translate('Subcategory')}}</label>
                    <div class="col-sm-3">
                        <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="subcategory_id[]" id="subcategory_id" multiple>
                            <option value="">{{translate('Select Subcategory')}}</option>
                        </select>
                    </div>
                </div>

                <!-- Brand Selector (Hidden by Default) -->
                <div class="form-group row" id="brand_selector" style="display: none;">
                    <label class="col-sm-3 col-from-label">{{translate('Brand')}}</label>
                    <div class="col-sm-3">
                        <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="brand_id[]" id="brand_id" multiple>
                            <option value="">{{translate('Select Brand')}}</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-info">{{translate('Export CSV')}}</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    // Show/hide selectors based on export type
    $('#export_type').change(function() {
        var exportType = $(this).val();

        // Hide all selectors
        $('#category_selector').hide();
        $('#subcategory_selector').hide();
        $('#brand_selector').hide();

        // Show the relevant selector
        if (exportType === 'category') {
            $('#category_selector').show();
            $('#subcategory_selector').show();
        } else if (exportType === 'brand') {
            $('#brand_selector').show();
        }
    });

    // Load subcategories when a category is selected
    $('#category_id').change(function() {
        var categoryIds = $(this).val(); // Get selected category IDs
        if (categoryIds && categoryIds.length > 0) {
            $.ajax({
                url: '{{ route("get_subcategories") }}',
                type: 'GET',
                data: { category_id: categoryIds },  // Correct parameter name
                success: function(response) {
                    var subcategorySelect = $('#subcategory_id');
                    subcategorySelect.empty(); // Clear existing options
                    subcategorySelect.append('<option value="">{{translate("Select Subcategory")}}</option>');

                    $.each(response, function(index, subcategory) {
                        subcategorySelect.append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
                    });

                    subcategorySelect.selectpicker('refresh'); // Refresh Select2/AIZ Selectpicker
                }
            });
        } else {
            $('#subcategory_id').html('<option value="">{{translate("Select Subcategory")}}</option>');
        }
    });
});

$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault();

        const excludeOutOfStock = $('#exclude_outofstock').is(':checked');

        const actionUrl = $(this).attr('action');
        const formData = $(this).serialize();
        const newUrl = actionUrl + '?' + formData + '&exclude_outofstock=' + excludeOutOfStock;

        window.location.href = newUrl;
    });
});
        function updateSettings(el, type){
            if($(el).is(':checked')){
                var value = 1;
            }
            else{
                var value = 0;
            }

            var bookeeypayValue = $('#bookeeypay').val();
            var upaymentValue = $('#upayment').val();
            if(type == "bookeeypay") {
                if($('#upayment').is(':checked')) {
                    AIZ.plugins.notify('danger', 'You can not enable both Bookeeypay and Upayment at the same time');
                    // set the value to 0
                    $('#bookeeypay').prop('checked', false);
                    return;
                }
            }else if(type == "upayment") {
                if($('#bookeeypay').is(':checked')) {
                    AIZ.plugins.notify('danger', 'You can not enable both Bookeeypay and Upayment at the same time');
                    $('#upayment').prop('checked', false);
                    return;
                }
            }

            $.post('{{ route('business_settings.update.activation') }}', {_token:'{{ csrf_token() }}', type:type, value:value}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }

</script>
@endsection
