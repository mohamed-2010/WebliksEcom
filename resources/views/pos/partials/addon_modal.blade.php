@php
    // $product is passed from the controller
    // $stock is also passed if needed
    // We'll parse the category_add_on_ids
@endphp

<div class="modal-header">
    <h5 class="modal-title">{{ $product->getTranslation('name') }}</h5>
</div>

<div class="modal-body">
    <!-- Hidden input to store the stock ID -->
    <input type="hidden" id="addon_stock_id" value="{{ $stock->id }}">

    @php
        $has_addons = (
            $product->category_add_on_ids != 'null' &&
            $product->category_add_on_ids != '' &&
            count(json_decode($product->category_add_on_ids)) > 0
        );
    @endphp

    @if ($has_addons)
        <div class="mb-3">
            <h5 class="opacity-70">{{ translate('Select your addons') }}</h5>
        </div>

        <div class="row">
            @foreach (json_decode($product->category_add_on_ids) as $category_add_on_id)
                @php
                    $category_add_on = \App\Models\CategoryAddon::find($category_add_on_id);
                @endphp

                @if ($category_add_on)
                    <div class="col-12 mb-3">
                        <label class="d-flex align-items-center">
                            <span class="mr-2">
                                {{ $category_add_on->getTranslation('name') }}
                                <small class="text-info ml-1">
                                    (+{{ single_price($category_add_on->price) }})
                                </small>
                            </span>

                            <div class="input-group input-group-sm ml-3" style="width: 120px;">
                                <div class="input-group-prepend">
                                    <button type="button"
                                            class="btn btn-light"
                                            onclick="changeAddonQty({{ $category_add_on->id }}, -1)">
                                        -
                                    </button>
                                </div>
                                <input type="text"
                                       class="form-control text-center addon-quantity"
                                       data-addon-id="{{ $category_add_on->id }}"
                                       data-addon-price="{{ $category_add_on->price }}"
                                       id="addon_qty_{{ $category_add_on->id }}"
                                       value="0">
                                <div class="input-group-append">
                                    <button type="button"
                                            class="btn btn-light"
                                            onclick="changeAddonQty({{ $category_add_on->id }}, 1)">
                                        +
                                    </button>
                                </div>
                            </div>
                        </label>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-center">
            <strong>{{ translate('No addons configured for this product.') }}</strong>
        </div>
    @endif
</div>

<script>
    function changeAddonQty(addonId, delta) {
        let input = document.getElementById('addon_qty_' + addonId);
        let val   = parseInt(input.value);
        let newVal= val + delta;
        if (newVal < 0) newVal = 0;
        input.value = newVal;
    }
</script>
