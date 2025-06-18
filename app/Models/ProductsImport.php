<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Utility\ProductUtility;
use Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
use MOIREI\GoogleMerchantApi\Facades\ProductApi;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    private $rows = 0;
    private $errors = [];
    private $isEditMode;

    public function __construct($isEditMode = false)
    {
        $this->isEditMode = $isEditMode;
    }

    public function collection(Collection $rows)
    {
        Log::info("Starting the import process");
        $canImport = true;
        $user = Auth::user();

        if ($user->user_type == 'seller' && addon_is_activated('seller_subscription')) {
            if ((count($rows) + $user->products()->count()) > $user->shop->product_upload_limit
                || $user->shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($user->shop->package_invalid_at), false) < 0
            ) {
                $canImport = false;
                flash(translate('Please upgrade your package.'))->warning();
            }
        }

        if ($canImport) {
            if ($this->isEditMode) {
                $firstRow = $rows->first();
                if (!isset($firstRow['id'])) {
                    $this->errors[] = 'The ID column is missing in the uploaded file.';
                    return;
                }
            }

            foreach ($rows as $row) {
                if ($this->isEditMode) {
                    $product = Product::find($row['id']);
                    if ($product) {
                        $newThumbnailPath = isset($row['thumbnail_img']) ? $this->downloadThumbnail($row['thumbnail_img']) : null;
                        $newPhotosPaths = isset($row['photos']) ? $this->downloadGalleryImages($row['photos']) : null;

                        $product->fill([
                            'name' => $row['name'] ?? $product->name,
                            'description' => $row['description'] ?? $product->description,
                            'category_id' => $row['category_id'] ?? $product->category_id,
                            'brand_id' => $row['brand_id'] ?? $product->brand_id,
                            'video_provider' => $row['video_provider'] ?? $product->video_provider,
                            'video_link' => $row['video_link'] ?? $product->video_link,
                            'tags' => $row['tags'] ?? $product->tags,
                            'unit_price' => $row['unit_price'] ?? $product->unit_price,
                            'purchase_price' => $row['purchase_price'] ?? $product->purchase_price,
                            'unit' => $row['unit'] ?? $product->unit,
                            // 'slug' => $row['slug'] ?? $product->slug,
                            'current_stock' => $row['current_stock'] ?? $product->current_stock,
                            'meta_title' => $row['meta_title'] ?? $product->meta_title,
                            'meta_description' => $row['meta_description'] ?? $product->meta_description,
                            'thumbnail_img' => $newThumbnailPath ?? $product->thumbnail_img,
                            'photos' => $newPhotosPaths ?? $product->photos,
                        ])->save();

                        // Update translations
                        $languages = $row['langs'] ? explode(',', $row['langs']) : [];
                        foreach ($languages as $language) {
                            $language = trim($language);

                            $translation = $product->product_translations()->where('lang', $language)->first();
                            if ($translation) {
                            if ($translation->lang == "en") {
                                $translation->update([
                                    'name' => $row['name'] ?? $translation->name,
                                    'description' => $row['description'] ?? $translation->description,
                                    'meta_title' => $row['meta_title'] ?? $translation->meta_title,
                                    'meta_description' => $row['meta_description'] ?? $translation->meta_description,
                                    // 'slug' => $row['slug'] ?? $translation->meta_description,
                                    'unit' => $row['unit'] ?? $translation->unit,
                                ]);
                            }
                            elseif ($translation->lang == "sa") {
                                $translation->update([
                                    'name' => $row['name_sa'] ?? $translation->name,
                                    'description' => $row['description_sa'] ?? $translation->description,
                                    'meta_title' => $row['meta_title_sa'] ?? $translation->meta_title,
                                    'meta_description' => $row['meta_description_sa'] ?? $translation->meta_description,
                                    // 'slug' => $row['slug'] ?? $translation->meta_description,
                                    'unit' => $row['unit'] ?? $translation->unit,
                                ]);
                            }
                        }
                        }
                        // Update product stock
                        $productStock = ProductStock::where('product_id', $product->id)->first();
                        if ($productStock) {
                            $productStock->update([
                                'qty' => $row['current_stock'] ?? 0,
                                'price' => $row['unit_price'] ?? 0,
                                'sku' => $row['sku'] ?? 0,
                            ]);
                        }
                    } else {
                        $this->errors[] = 'Row ' . ($this->rows + 1) . ': Product with ID ' . $row['id'] . ' not found.';
                    }

                } else {
                    $approved = 1;
                    if ($user->user_type == 'seller' && get_setting('product_approve_by_admin') == 1) {
                        $approved = 0;
                    }

                    $productId = Product::create([
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'added_by' => $user->user_type == 'seller' ? 'seller' : 'admin',
                        'user_id' => $user->user_type == 'seller' ? $user->id : User::where('user_type', 'admin')->first()->id,
                        'approved' => $approved,
                        'category_id' => $row['category_id'],
                        'brand_id' => $row['brand_id'],
                        'video_provider' => $row['video_provider'],
                        'video_link' => $row['video_link'],
                        'tags' => $row['tags'],
                        'unit_price' => $row['unit_price'],
                        'unit' => $row['unit'],
                        'meta_title' => $row['meta_title'] != null ? $row['meta_title'] : preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($row['name']))),
                        'meta_description' => $row['meta_description'] != null && $row['meta_description'] != '' ? $row['meta_description'] :
                            ProductUtility::htmlToText($row['description']),
                        // preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($row['description']))),
                        'colors' => json_encode(array()),
                        'choice_options' => json_encode(array()),
                        'variations' => json_encode(array()),
                        'slug' => $row['slug'] == null ? preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($row['name']))) . '-' . Str::random(5) : preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($row['slug']))) . '-' . Str::random(5),
                        'thumbnail_img' => $this->downloadThumbnail($row['thumbnail_img']),
                        'photos' => $this->downloadGalleryImages($row['photos']),
                    ]);
                    ProductStock::create([
                        'product_id' => $productId->id,
                        'qty' => $row['current_stock'],
                        'price' => $row['unit_price'],
                        'sku' => $row['sku'],
                        'variant' => '',
                    ]);

                    // ProductTranslation::create([
                    //     'product_id' => $productId->id,
                    //     'name' => $row['name'],
                    //     'description' => $row['description'],
                    //     'meta_title' => $row['meta_title'],
                    //     'meta_description' => $row['meta_description'],
                    //     'locale' => 'en',
                    // ]);

                    // will loop for languages in excel file and create translations
                    $languages = $row['langs'] ? explode(',', $row['langs']) : [];
                    // Log::alert($languages);
                    foreach ($languages as $language) {
                        $product_translation = ProductTranslation::create([
                            'product_id' => $productId->id,
                            'name' => $language == 'en' ? $row['name'] : $row['name_' . $language],
                            'description' => $language == 'en'
                                ? $row['description']
                                    : $row['description_' . $language],
                            'meta_title' => $language == 'en'
                                ? ($row['meta_title'] != null && $row['meta_title'] != ''
                                    ? $row['meta_title']
                                    : preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($row['name']))))
                                : ($row['meta_title_' . $language] != null && $row['meta_title_' . $language] != ''
                                    ? $row['meta_title_' . $language]
                                    : str_replace(' ', '-', strtolower($row['name_' . $language]))),
                            'meta_description' => $language == 'en' ? ($row['meta_description'] != null && $row['meta_description'] != ''
                                ? $row['meta_description']
                                : ProductUtility::htmlToText($row['description']))
                                : ($row['meta_description_' . $language] != null && $row['meta_description_' . $language] != ''
                                    ? $row['meta_description_' . $language]
                                    : ProductUtility::htmlToText($row['description_' . $language])),
                            'unit' => $language == 'en' ? $row['unit'] : $row['unit_' . $language],
                            'lang' => $language,
                            'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($row['name']))) . '-' . Str::random(5)
                        ]);
                        // Log::info($product_translation);
                    }
                    $facebookAccessToken = config('services.facebook.access_token');
                    $facebookCatalogId = config('services.facebook.catalog_id');

                if (get_setting('google_merchant') == 1) {
                        $product = [
                            'id' => $productId->id,
                            'title' => $row['name'],
                            'link' => route('product', ['slug' => $product_translation->slug]),
                            'description' => $row['description'],
                            'image_link' => $row['thumbnail_img'],
                            'price' => $row['unit_price'],
                        ];
                        ProductApi::insert($product);
                    }
                if (!empty($facebookAccessToken) && !empty($facebookCatalogId) && get_setting('facebook_commerce') == 1) {
                        $facebookService = app(\App\Services\FacebookMerchantService::class);
                        $facebookService->createOrUpdateProduct($productId);
                    }

                }
                    // ++$this->rows;
                }

                if (empty($this->errors)) {
                    flash(translate('Products imported successfully'))->success();
                }
            }
        }

    public function model(array $row)
    {
        ++$this->rows;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function rules(): array
    {
        return [
            'unit_price' => function ($attribute, $value, $onFailure) {
                if (!is_numeric($value)) {
                    $onFailure('Unit price is not numeric');
                }
            },

            'name' => function ($attribute, $value, $onFailure) {
                if (empty($value)) {
                    $onFailure('Name is required');
                }
            },

            'category_id' => function ($attribute, $value, $onFailure) {
                if (!is_numeric($value)) {
                    $onFailure('Category ID must be numeric');
                }
                if (empty($value)) {
                    $onFailure('Category ID is required');
                }
            },

            'tags' => function ($attribute, $value, $onFailure) {
                if (!empty($value) && !is_string($value)) {
                    $onFailure('Tags must be a string');
                }
            },
        ];
    }

    public function downloadThumbnail($url)
    {
        try {
            $relativePath = str_replace(url('/') . '/', '', $url);
            $relativePath = str_replace('public/', '', $relativePath);

            $upload = Upload::where('file_name', $relativePath)->first();
            if ($upload){
                return $upload->id;
            }
            $upload = new Upload;
            $upload->external_link = $url;
            $upload->type = 'image';

            $upload->save();

            return $upload->id;
        } catch (\Exception $e) {
        }
        return null;
    }

    public function downloadGalleryImages($urls)
    {
        $data = array();
        foreach (explode(',', str_replace(' ', '', $urls)) as $url) {
            $data[] = $this->downloadThumbnail($url);
        }
        return implode(',', $data);
    }
}
