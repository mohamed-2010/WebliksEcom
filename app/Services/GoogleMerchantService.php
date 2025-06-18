<?php

namespace App\Services;

use App\Models\Product;
use Google_Client;
use Google_Service_ShoppingContent;
use Google_Service_ShoppingContent_Product;
use Log;

class GoogleMerchantService
{
    protected $client;
    protected $merchantId;
    protected $service;

    public function __construct()
    {
        $this->merchantId = config('services.google.merchant_id');

        $client = new Google_Client();
        $client->setApplicationName('Webliks');
        $client->setScopes(['https://www.googleapis.com/auth/content']);
        // check if the json file exists
        if (!file_exists(public_path(env('GOOGLE_SERVICE_ACCOUNT_JSON')))) {
            throw new \Exception("Google service account JSON file not found");
        }
        $client->setAuthConfig(public_path(env('GOOGLE_SERVICE_ACCOUNT_JSON')));
        $client->addScope(Google_Service_ShoppingContent::CONTENT);

        $this->client = $client;
        $this->service = new Google_Service_ShoppingContent($this->client);
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

            $inStock = $product->current_stock > 0 ? "in stock" : "out of stock";


            $googleProduct = new \Google_Service_ShoppingContent_Product([
                'offerId' => "online:en:KWD:{$product->id}",
                'title' => $product->name,
                'description' => $product->getTranslation('description', 'en'),
                'link' => $defaultProductUrl,
                'imageLink' => $imageUrl[0] ?? null,
                'additionalImageLinks' => array_slice($imageUrl, 1),
                'contentLanguage' => 'en',
                'targetCountry' => 'KWD',
                'channel' => 'online',
                'availability' => $inStock,
                'condition' => 'new',
                'price' => [
                    'value' => $product->unit_price,
                    'currency' => 'KWD',
                ]
            ]);

            $response = $this->service->products->insert($this->merchantId, $googleProduct);
            Log::info("Product inserted: " . json_encode($response));
            return $response;
        } catch (\Google_Service_Exception $e) {
            Log::error("Google Merchant insert error: " . $e->getMessage());
            return null;
        }


        return $this->service->products->insert($this->merchantId, $googleProduct);
    }

    public function deleteProduct($productId)
    {
        return $this->service->products->delete($this->merchantId, "online:en:KWD:{$productId}");
    }
}
