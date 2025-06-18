<div class="card-header mb-2">
    <h3 class="h6">{{translate('Set Club Points for Selected Categories')}}</h3>
</div>
<div class="product-choose-list">
    <div class="product-choose">
        <div class="form-group row">
            <label class="col-lg-3 col-from-label" for="name">{{translate('Categories')}}</label>
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
<div class="form-group row">
    <label class="col-lg-3 col-from-label" for="club_point">{{translate('Club Point')}}</label>
    <div class="col-lg-9">
        <input type="number" lang="en" name="club_point" value="" class="form-control" required>
    </div>
</div>
