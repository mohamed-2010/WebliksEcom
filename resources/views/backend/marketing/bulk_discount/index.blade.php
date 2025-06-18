
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Bulk Discount')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table p-0">
              <thead>
                  <tr>
                      <th data-breakpoints="lg">#</th>
                      <th data-breakpoints="lg">{{translate('Categries')}}</th>
                      <th data-breakpoints="lg">{{translate('Start Date')}}</th>
                      <th data-breakpoints="lg">{{translate('End Date')}}</th>
                      <th data-breakpoints="lg">{{translate('Discount')}}</th>
                      <th data-breakpoints="lg">{{translate('Discount Type')}}</th>
                      <th width="10%">{{translate('Options')}}</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach($discounts as $key => $discount)
                  @php
                    $cat_ids = [];
                    $cat_names = [];
                    if (isset($discount->category_ids) && $discount->category_ids != null) {
                        $cat_ids = json_decode($discount->category_ids, true) ?? [];
                        if (!empty($cat_ids) && is_array($cat_ids)) {
                            $cat_names = App\Models\Category::whereIn('id', $cat_ids)->pluck('name');
                        }
                    }
                    if (isset($discount->brand_ids) && $discount->brand_ids != null) {
                        $brand_ids = json_decode($discount->brand_ids, true) ?? [];
                        if (!empty($brand_ids) && is_array($brand_ids)) {
                            $cat_names = App\Models\Brand::whereIn('id', $brand_ids)->pluck('name');
                        }
                    }
                  @endphp
                      <tr>
                          <td>{{$key+1}}</td>
                          <td>
                            @foreach($cat_names as $category)
                            {{ $category }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                          </td>

                          <td>{{   $discount->date_start  }}</td>
                          <td>{{   $discount->date_end  }}</td>
                          <td>{{   $discount->discount  }}</td>
                          <td>{{   $discount->discount_type  }}</td>
                          <td class="text-right">
                              @can('edit_discount')
                                  <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('products.bulk-discount-form-edit', encrypt($discount->id) )}}" title="{{ translate('Edit') }}">
                                      <i class="las la-edit"></i>
                                  </a>
                              @endcan
                              @can('delete_discount')
                              <a href="javascript:void(0);" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                              title="{{ translate('Delete') }}"
                              onclick="confirmDelete({{ $discount->id }});">
                              <i class="las la-trash"></i>
                              </a>

                                <form id="delete-form-{{ $discount->id }}" action="{{ route('products.bulk-discount-form-delete', base64_encode($discount->id)) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('POST')
                                </form>
                           @endcan
                          </td>
                      </tr>
                  @endforeach
              </tbody>
          </table>
      </div>
  </div>
