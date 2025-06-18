@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="mb-0 h6">{{ translate('Bulk Discount Update') }}</h1>
</div>

<div class="card">
    <form action="{{ route('products.bulk-discount-form-new-update',base64_encode($bulk_discount->id)) }}" method="POST">
        @csrf
        @php
            $cat_ids=json_decode($bulk_discount->category_ids ?? '[]');
            $brand_ids=json_decode($bulk_discount->brand_ids ?? '[]');
         @endphp
        <div class="card-body">
            <div class="form-group row">
                <label class="col-lg-3 col-from-label">{{ translate('Categories') }}</label>
                <div class="col-lg-8">
                    <select class="form-control aiz-selectpicker"
                    name="category_ids[]" multiple data-live-search="true">
                    @foreach (\App\Models\Category::where('parent_id', 0)->with('childrenCategories')->get() as $category)
                        <option
                            value="{{ $category->id }}"
                            @if($cat_ids != null && in_array($category->id, $cat_ids)) selected @endif>
                            {{ $category->getTranslation('name') }}
                        </option>
                        @foreach ($category->childrenCategories as $childCategory)
                            <option
                                value="{{ $childCategory->id }}"
                                @if($cat_ids!= null && in_array($childCategory->id, $cat_ids)) selected @endif>
                                 -- {{ $childCategory->getTranslation('name') }}
                            </option>
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
                            <option value="{{ $brand->id }}" @if($brand_ids != null && in_array($brand->id, $brand_ids)) selected @endif>{{ $brand->getTranslation('name') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-from-label" for="start_date">{{translate('Discount Date Range')}}</label>
                <div class="col-sm-9">
                  <input type="timedate" class="form-control aiz-date-range" @if($bulk_discount->date_start && $bulk_discount->date_end) value="{{ $bulk_discount->date_start.' to '.$bulk_discount->date_end }}" @endif name="date_range" placeholder="{{translate('Select Date')}}" data-time-picker="true" data-format="DD-MM-Y HH:mm:ss" data-separator=" to " autocomplete="off">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-from-label">{{ translate('Discount') }}</label>
                <div class="col-lg-6">
                    <input type="number" value="{{ $bulk_discount->discount }}" lang="en" min="0" step="0.01" placeholder="{{ translate('Discount') }}" name="discount" class="form-control" required>
                </div>
                <div class="col-lg-3">
                    <select class="form-control aiz-selectpicker" name="discount_type" required>
                        <option @if($bulk_discount=='amount') selected @endif value="amount">{{ translate('Flat') }}</option>
                        <option @if($bulk_discount=='percent') selected @endif value="percent">{{ translate('Percent') }}</option>
                    </select>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">{{ translate('Update Discounts') }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
