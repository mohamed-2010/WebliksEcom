<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{  translate('INVOICE') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
	<style media="all">
        @page {
			margin: 0;
			padding:0;
		}
		body{
			font-size: 0.875rem;
            font-family: '<?php echo  $font_family ?>';
            font-weight: normal;
            direction: <?php echo  $direction ?>;
            text-align: <?php echo  $text_align ?>;
			padding:0;
			margin:0;
		}
		.gry-color *,
		.gry-color{
			color:#000;
		}
		table{
			width: 100%;
		}
		table th{
			font-weight: normal;
		}
		table.padding th{
			padding: .25rem .7rem;
		}
		table.padding td{
			padding: .25rem .7rem;
		}
		table.sm-padding td{
			padding: .1rem .7rem;
		}
		.border-bottom td,
		.border-bottom th{
			border-bottom:1px solid #eceff4;
		}
		.text-left{
			text-align:<?php echo  $text_align ?>;
		}
		.text-right{
			text-align:<?php echo  $not_text_align ?>;
		}
	</style>
</head>
<body>
	<div>

		@php
			$logo = get_setting('header_logo');
		@endphp

		<div style="background: #eceff4;padding: 1rem;">
			<table>
				<tr>
					<td>
						@if($logo != null)
							<img src="{{ uploaded_asset($logo) }}" height="30" style="display:inline-block;">
						@else
							<img src="{{ asset('assets/img/logo.png') }}" height="30" style="display:inline-block;">
						@endif
					</td>
					<td style="font-size: 1.5rem;" class="text-right strong">{{  translate('INVOICE') }}</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1rem;" class="strong">{{ get_setting('site_name') }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{ get_setting('contact_address') }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Email') }}: {{ get_setting('contact_email') }}</td>
					<td class="text-right small"><span class="gry-color small">{{  translate('Order ID') }}:</span> <span class="strong">{{ $order->code }}</span></td>
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Phone') }}: {{ get_setting('contact_phone') }}</td>
					<td class="text-right small"><span class="gry-color small">{{  translate('Order Date') }}:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span></td>
				</tr>
				<tr>
					<td class="gry-color small"></td>
					<td class="text-right small">
                        <span class="gry-color small">
                            {{  translate('Payment method') }}:
                        </span>
                        <span class="strong">
                            @if($order->payment_type == 'Wallet_upay')
                                {{ translate('Wallet & upay') }}
                            @else
                                {{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}
                            @endif
                        </span>

                    </td>
				</tr>
			</table>
		</div>

		<div style="padding: 1rem;padding-bottom: 0">
			<table>
				@php
					$shipping_address = json_decode($order->shipping_address);
				@endphp
				@if($shipping_address)
					<tr>
						<td class="strong small gry-color">{{ translate('Bill to') }}:</td>
					</tr>
					<tr>
						<td>
							<address>
								<table>
									<tr>
										<td class="strong">{{ translate('Name') }}:</td>
										<td>{{ $shipping_address->name ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Email') }}:</td>
										<td>{{ $shipping_address->email ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Phone') }}:</td>
										<td>{{ $shipping_address->phone ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Type') }}:</td>
										<td>{{ $shipping_address->address_type ?? '' }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('State') }}:</td>
										<td>{{ $shipping_address->state ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('City') }}:</td>
										<td>{{ $shipping_address->city ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Block') }}:</td>
										<td>{{ $shipping_address->bloc ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Avenue') }}:</td>
										<td>{{ $shipping_address->avenue ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Street') }}:</td>
										<td>{{ $shipping_address->street ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('House') }}:</td>
										<td>{{ $shipping_address->house ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Address') }}:</td>
										<td>{{ $shipping_address->address ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Address Label') }}:</td>
										<td>{{ $shipping_address->address_label ?? "" }}</td>
									</tr>
									<tr>
										<td class="strong">{{ translate('Building Number') }}:</td>
										<td>
											@if($shipping_address->address_type == "apartment" || $shipping_address->address_type == "office")
												<span class="strong">{{ translate('building Number. or building name') }}:</span>
											@else
												<span class="strong">{{ translate('House Number') }}:</span>
											@endif
											{{ $shipping_address->building_number ?? "" }}
										</td>
									</tr>
									@if(json_decode($order->shipping_address)->address_type != "house")
										<tr>
											<td class="strong">{{ translate('Floor') }}:</td>
											<td>{{ $shipping_address->floor ?? '' }}</td>
										</tr>
										<tr>
											<td class="strong">{{ translate('Apt.Number') }}:</td>
											<td>{{ $shipping_address->apt_number ?? '' }}</td>
										</tr>
									@endif
								</table>
							</address>
						</td>
					</tr>
				@endif
			</table>
		</div>

	    <div style="padding: 1rem;">
			<table class="padding text-left small border-bottom">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
	                    <th width="35%" class="text-left">{{ translate('Product Name') }}</th>
						<th width="15%" class="text-left">{{ translate('Delivery Type') }}</th>
	                    <th width="10%" class="text-left">{{ translate('Qty') }}</th>
	                    <th width="15%" class="text-left">{{ translate('Unit Price') }}</th>
	                    <th width="10%" class="text-left">{{ translate('Tax') }}</th>
	                    <th width="15%" class="text-right">{{ translate('Total') }}</th>
	                </tr>
				</thead>
				<tbody class="strong">
	                @foreach ($order->orderDetails as $key => $orderDetail)
		                @if ($orderDetail->product != null)
							<tr class="">
								<td>
                                    {{ $orderDetail->product->name }}
                                    @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif
                                    <br>
                                    <small>
                                        @php
                                            $product_stock = json_decode($orderDetail->product->stocks->first(), true);
                                        @endphp
                                        {{translate('SKU')}}: {{ $product_stock['sku'] }}
                                    </small>
                                </td>
								<td>
									@if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
										{{ translate('Home Delivery') }}
									@elseif ($order->shipping_type == 'pickup_point')
										@if ($order->pickup_point != null)
											{{ $order->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
										@else
                                            {{ translate('Pickup Point') }}
										@endif
									@elseif ($order->shipping_type == 'carrier')
										@if ($order->carrier != null)
											{{ $order->carrier->name }} ({{ translate('Carrier') }})
											<br>
											{{ translate('Transit Time').' - '.$order->carrier->transit_time }}
										@else
											{{ translate('Carrier') }}
										@endif
									@endif
								</td>
								<td class="">{{ $orderDetail->quantity }}</td>
								<td class="currency">{{ single_price($orderDetail->price/$orderDetail->quantity) }}</td>
								<td class="currency">{{ single_price($orderDetail->tax/$orderDetail->quantity) }}</td>
			                    <td class="text-right currency">{{ single_price($orderDetail->price+$orderDetail->tax) }}</td>
							</tr>
		                @endif
					@endforeach
	            </tbody>
			</table>
		</div>

        <div style="padding:0 1.5rem;">
            <table class="text-right sm-padding small strong">
                <thead>
                    <tr>
                        <th width="60%"></th>
                        <th width="40%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left">
                            @php
                                $removedXML = '<?xml version="1.0" encoding="UTF-8"?>';
                            @endphp
                            {!! str_replace($removedXML, '', QrCode::size(100)->generate($order->code)) !!}
                        </td>
                        <td>
                            <table class="text-right sm-padding small strong">
                                <tbody>
                                    <tr>
                                        <th class="gry-color text-left">{{ translate('Sub Total') }}</th>
                                        <td class="currency">{{ single_price($order->orderDetails->sum('price')) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="gry-color text-left">{{ translate('Shipping Cost') }}</th>
                                        <td class="currency">{{ single_price($order->orderDetails->sum('shipping_cost')) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="gry-color text-left">{{ translate('Total Tax') }}</th>
                                        <td class="currency">{{ single_price($order->orderDetails->sum('tax')) }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="gry-color text-left">{{ translate('Coupon Discount') }}</th>
                                        <td class="currency">{{ single_price($order->coupon_discount) }}</td>
                                    </tr>
                                    @if ($wallet_balance > 0)
                                        <tr class="border-bottom">
                                            <th class="gry-color text-left">{{ translate('Wallet Balance') }}</th>
                                            <td class="currency">{{ single_price($wallet_balance) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th class="text-left strong">{{ translate('Grand Total') }}</th>
                                        <td class="currency">
                                            @php
                                                $subtotal = $order->orderDetails->sum('price');
                                                $shipping = $order->orderDetails->sum('shipping_cost');
                                                $tax = $order->orderDetails->sum('tax');
                                                $coupon_discount = $order->coupon_discount;
                                                $total = $subtotal + $shipping + $tax - $coupon_discount - $wallet_balance;
                                            @endphp
                                            {{ single_price($total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

	</div>
</body>
</html>
