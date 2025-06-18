<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\ResponseCodeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductUpdatePriceRequest;
use App\Http\Requests\Api\ProductUpdateQuantityRequest;
use App\Http\Requests\Api\StocktUpdatePriceRequest;
use App\Http\Requests\Api\StocktUpdateQuantityRequest;
use App\Models\Product;
use App\Models\ProductStock;
use App\Traits\ApiResponseTrait;



class ProductBindOracleController extends Controller
{
    use ApiResponseTrait;

    public function products()
    {
        $products =  Product::select(['id', 'name', 'unit_price', 'current_stock'])
            ->with('stocks:id,product_id,variant,sku,price,qty')->get();

        return $this->dataResponse($products, ResponseCodeEnum::OK->value,'success');
    }

    public function updateProductQuantity(ProductUpdateQuantityRequest $request, Product $product)
    {
        $this->update($product, [
            'current_stock' => $request->current_stock
        ]);
    }// End updateProduct

    public function updateProductPrice(ProductUpdatePriceRequest $request, Product $product)
    {
        $this->update($product, [
            'unit_price' => $request->price
        ]);
    }// End updateProductPrice

    public function updateStockVariantPrice(StocktUpdatePriceRequest $request ,ProductStock $productStock)
    {
        $this->update($productStock, [
            'price' => $request->price
        ]);
    }// End updateStockVariant

    public function updateStockVariantQuantity(StocktUpdateQuantityRequest $request , ProductStock $productStock)
    {
        $this->update($productStock, [
            'qty' => $request->current_stock
        ]);
    }// End updateStockVariantQuantity


    private function update(object $model,array $request)
    {
        try {
            $model->update($request);
            return $this->successResponse('Updated Success', ResponseCodeEnum::OK->value);
        } catch (\Exception|\Error $e) {
            return $this->errorResponse('Error message detailing the issue', ResponseCodeEnum::BAD_REQUEST->value);
        }
    }// End update

}
