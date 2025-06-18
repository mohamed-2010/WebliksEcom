<div class="card rounded border-0 shadow-sm">
    <div class="card-header">
        <h3 class="fs-16 fw-600 mb-0">{{ translate('Summary') }}</h3>
        <div class="text-right">
            <span class="badge badge-inline badge-primary">
                {{ count($carts) }}
                {{ translate('Items') }}
            </span>
            @php
                $coupon_discount = 0;
            @endphp
            @if (get_setting('coupon_system') == 1)
                @php
                    $coupon_code = null;
                @endphp

                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = \App\Models\Product::find($cartItem['product_id']);
                    @endphp
                    @if ($cartItem->coupon_applied == 1)
                        @php
                            $coupon_code = $cartItem->coupon_code;
                            break;
                        @endphp
                    @endif
                @endforeach

                @php
                    $coupon_discount = carts_coupon_discount($coupon_code, false, true);
                @endphp
            @endif

            @php $subtotal_for_min_order_amount = 0; @endphp
            @foreach ($carts as $key => $cartItem)
                @php $subtotal_for_min_order_amount += cart_product_price($cartItem, $cartItem->product, false, false) * $cartItem['quantity']; @endphp
            @endforeach

            @if (get_setting('minimum_order_amount_check') == 1 && $subtotal_for_min_order_amount < get_setting('minimum_order_amount'))
                <span class="badge badge-inline badge-primary">
                    {{ translate('Minimum Order Amount') . ' ' . single_price(get_setting('minimum_order_amount')) }}
                </span>
            @endif
        </div>
    </div>

    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th class="product-name">{{ translate('Product') }}</th>
                    <th class="product-total text-right">{{ translate('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $tax = 0;
                    $shipping = 0;
                    $product_shipping_cost = 0;
                    //$shipping_region = $shipping_info['city'];
                @endphp
                
                @foreach ($carts as $key => $cartItem)
                    @php
                        $product = \App\Models\Product::find($cartItem['product_id']);
                        $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $product_shipping_cost = $cartItem['shipping_cost'];
                        $addons_total = 0;
                        
                        $shipping += $product_shipping_cost;
                        
                        $product_name_with_choice = $product->getTranslation('name');
                        if ($cartItem['variant'] != null) {
                            $product_name_with_choice = $product->getTranslation('name') . ' - ' . $cartItem['variant'];
                        }
                        if($cartItem['addons'] != null || $cartItem['addons'] != "") {
                            foreach(json_decode($cartItem['addons']) as $addon){
                                $subtotal += $addon->price*(int)$addon->quantity;
                                $addons_total += $addon->price*(int)$addon->quantity;
                            }
                        }
                    @endphp
                    <tr class="cart_item">
                        <td class="product-name col">
                            {{ $product_name_with_choice }}
                            <strong class="product-quantity">
                                × {{ $cartItem['quantity'] }}
                            </strong>
                            {{--addons--}}
                            @foreach (json_decode($cartItem['addons']) as $key => $addon)
                                @if($addon->quantity > 0)
                                    @php
                                        $addon = \App\Models\CategoryAddon::find($addon->id);
                                    @endphp
                                    <strong class="addon-name">{{ $addon->getTranslation('name') }}: </strong>
                                    @if ($addon->choice_options != null)
                                        {{ $addon->choice_options[$addon->choices]->title }}
                                    @else
                                        {{ single_price($addon->price) }}
                                    @endif
                                    @if ($key != count(json_decode($cartItem['addons'])) - 1)
                                        <br>
                                    @endif
                                @endif
                            @endforeach
                        </td>
                        <td class="product-total text-right">
                            <span
                                class="pl-4 pr-0">{{ single_price((cart_product_price($cartItem, $cartItem->product, false, false) * $cartItem['quantity']) + $addons_total) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <input type="hidden" id="sub_total" value="{{ $subtotal ?? 0 }}">
        <table class="table">

            <tfoot>
                <tr class="cart-subtotal">
                    <th>{{ translate('Subtotal') }}</th>
                    <td class="text-right">
                        <span class="fw-600">{{ single_price($subtotal ?? 0) }}</span>
                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{ translate('Tax') }}</th>
                    <td class="text-right">
                        <span class="font-italic">{{ single_price($tax ?? 0) }}</span>
                    </td>
                </tr>

                <tr class="cart-shipping">
                    <th>{{ translate('Total Shipping') }}</th>
                    <td class="text-right">
                        <span class="font-italic">{{ single_price($shipping ?? 0) }}</span>
                    </td>
                </tr>

                @if ($coupon_discount > 0)
                    <tr class="cart-shipping">
                        <th>{{ translate('Coupon Discount') }}</th>
                        <td class="text-right">
                            <span class="font-italic">{{ single_price($coupon_discount ?? 0) }}</span>
                        </td>
                    </tr>
                @endif

                @php
                    $total = $subtotal+$tax+$shipping;
                    if ($carts->sum('discount') > 0){
                        $total -= $carts->sum('discount');
                    }
                @endphp

                <tr class="cart-total">
                    <th><span class="strong-600">{{ translate('Total') }}</span></th>
                    <td class="text-right">
                        <strong><span>{{ single_price($total) }}</span></strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        @if (get_setting('coupon_system') == 1)
            @if ($coupon_discount > 0 && $coupon_code)
                <div class="mt-3">
                    <form class="" id="remove-coupon-form" action="{{ route('checkout.remove_coupon_code_guest') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <div class="form-control">{{ $coupon_code }}</div>
                            <div class="input-group-append">
                                <button type="button" id="coupon-remove"
                                    class="btn btn-primary">{{ translate('Change Coupon') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="mt-3">
                    <form class="" id="apply-coupon-form" {{--action="{{ route('checkout.apply_coupon_code_guest') }}" method="POST"--}} enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="owner_id" value="{{ $carts[0]['owner_id'] }}">
                        <div class="input-group">
                            <input type="text" class="form-control" name="code"
                                onkeydown="return event.key != 'Enter';"
                                placeholder="{{ translate('Have coupon code? Enter here') }}" required>
                            <div class="input-group-append">
                                <button type="button" id="coupon-apply"
                                    class="btn btn-primary">{{ translate('Apply') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        @endif

    </div>
</div>
