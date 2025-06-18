@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="mb-0 h6">{{ translate('Bulk Discount Update') }}</h1>
</div>

<div class="card">
    <form action="{{ route('products.bulk-discount-update') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group row">
                <label class="col-lg-3 col-from-label">{{ translate('Categories') }}</label>
                <div class="col-lg-8">
                    <select class="form-control aiz-selectpicker" name="category_ids[]" multiple data-live-search="true">
                        @foreach (\App\Models\Category::where('parent_id', 0)->with('childrenCategories')->get() as $category)
                            <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                            @foreach ($category->childrenCategories as $childCategory)
                                @include('backend.product.categories.child_category', ['child_category' => $childCategory])
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-from-label">{{ translate('Brands') }}</label>
                <div class="col-lg-8">
                    <select class="form-control aiz-selectpicker" name="brand_ids[]" multiple data-live-search="true">
                        @foreach (\App\Models\Brand::all() as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-from-label" for="start_date">{{ translate('Discount Date Range') }}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control aiz-date-range" name="date_range" placeholder="{{ translate('Select Date') }}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-from-label">{{ translate('Discount') }}</label>
                <div class="col-lg-6">
                    <input type="number" lang="en" min="0" step="0.01" placeholder="{{ translate('Discount') }}" name="discount" class="form-control" required>
                </div>
                <div class="col-lg-3">
                    <select class="form-control aiz-selectpicker" name="discount_type" required>
                        <option value="amount">{{ translate('Flat') }}</option>
                        <option value="percent">{{ translate('Percent') }}</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-from-label">{{ translate('Enable Discount on Club Point') }}</label>
                <div class="col-lg-9">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" id="earn_point_switch" name="apply_to_club_point">
                        <span></span>
                    </label>
                </div>
            </div>

            <div class="form-group row d-none" id="earn_point_discount_section">
                <label class="col-lg-3 col-from-label">{{ translate('Club Point Discount') }}</label>
                <div class="col-lg-6">
                    <input type="number" lang="en" min="0" step="0.01" placeholder="{{ translate('Discount on Club Point') }}" name="club_point_discount" class="form-control">
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">{{ translate('Update Discounts') }}</button>
            </div>
        </div>
    </form>
</div>
<div class="card">
    @include('backend.marketing.bulk_discount.index')
</div>
@endsection
@section('script')
<script>
    function confirmDelete(discountId) {
        // Display confirmation dialog
        if (confirm("Are you sure you want to delete this discount?")) {
            // If confirmed, submit the form
            document.getElementById('delete-form-' + discountId).submit();
        }
    }
</script>

<script>
    $(document).ready(function() {
        $('#earn_point_switch').on('change', function() {
            if ($(this).is(':checked')) {
                $('#earn_point_discount_section').removeClass('d-none');
            } else {
                $('#earn_point_discount_section').addClass('d-none');
            }
        });
    });
</script>
@endsection
