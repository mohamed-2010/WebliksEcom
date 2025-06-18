<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PosProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {

                $hasAddons = false;
                if (!empty($data->category_add_on_ids) && $data->category_add_on_ids != 'null') {
                    $addonIds = json_decode($data->category_add_on_ids);
                    if (is_array($addonIds) && count($addonIds) > 0) {
                        $hasAddons = true;
                    }
                }

                return [
                    'id' => $data->id,
                    'stock_id' => $data->stock_id,
                    'name' => $data->name,
                    'thumbnail_image' => ($data->stock_image == null)  ? uploaded_asset($data->thumbnail_img) : uploaded_asset($data->stock_image),
                    'price' => home_discounted_base_price_by_stock_id($data->stock_id),
                    'base_price' => home_base_price_by_stock_id($data->stock_id),
                    'qty' => $data->stock_qty,
                    'variant' => $data->variant,
                    'has_addons' => $hasAddons ? 1 : 0,
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
