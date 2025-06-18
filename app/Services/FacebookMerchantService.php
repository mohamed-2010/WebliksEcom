<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookMerchantService
{
    protected $accessToken;
    protected $catalogId;

    public function __construct()
    {
        $this->accessToken = config('services.facebook.access_token');
        $this->catalogId = config('services.facebook.catalog_id');
    }

    public function createOrUpdateProduct($product)
    {
        try {
            if (is_numeric($product)) {
                $product = Product::findOrFail($product);
            }

            $baseUrl = env('APP_URL', 'https://localhost/demo');
            $defaultProductUrl = "{$baseUrl}/product/{$product->slug}";

            $photoIds = json_decode($product->photos, true);

            if (!is_array($photoIds)) {
                $photoIds = is_numeric($photoIds) ? [$photoIds] : [];
            }

            $imageUrl = \App\Models\Upload::whereIn('id', $photoIds)->get()->map(function ($upload) use ($baseUrl) {
                return !empty($upload->file_name) ? "{$baseUrl}/public/{$upload->file_name}" : $upload->external_link;
            })->toArray();

            $availability = $product->current_stock > 0 ? "in stock" : "out of stock";

            $payload = [
                'retailer_id' => $product->id,
                'name' => $product->name,
                'description' => $product->getTranslation('description', 'en'),
                'url' => $defaultProductUrl,
                'availability' => $availability,
                'condition' => 'new',
                'price' => $product->unit_price . ' KWD',
                'brand' => 'Your Brand',
            ];
            if (empty($imageUrl)) {
                Log::warning("No valid images found for product ID: {$product->id}");
            }
            $payload['image_url'] = $imageUrl[0] ?? null;

            $endpoint = "https://graph.facebook.com/v19.0/{$this->catalogId}/products";

            $response = Http::post($endpoint, array_merge($payload, [
                'access_token' => $this->accessToken,
            ]));

            if ($response->successful()) {
                Log::info("Facebook product added: " . $response->body());
                return $response->json();
            } else {
                Log::error("Facebook product error: " . $response->body());
                return null;
            }

        } catch (\Exception $e) {
            Log::error("FacebookMerchantService error: " . $e->getMessage());
            throw new \Exception("Failed to sync product to Facebook: " . $e->getMessage());
            return null;
        }
    }

    public function deleteProduct($productId)
    {
        $endpoint = "https://graph.facebook.com/v19.0/{$this->catalogId}/products/{$productId}";
        $response = Http::delete($endpoint, [
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            Log::info("Facebook product deleted: {$productId}");
            return true;
        }

        Log::error("Failed to delete Facebook product: " . $response->body());
        return false;
    }
}
