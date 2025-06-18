<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Language;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithMapping, WithHeadings, WithStyles
{
    private $exportType;
    private $categoryId;
    private $subcategoryId;
    private $brandId;
    private $excludeOutOfStock;


    public function __construct($exportType = 'all', $categoryId = null, $subcategoryId = null, $brandId = null, $excludeOutOfStock = false)
    {
        $this->exportType = $exportType;
        $this->categoryId = $categoryId;
        $this->subcategoryId = $subcategoryId;
        $this->brandId = $brandId;
        $this->excludeOutOfStock = $excludeOutOfStock;
    }

    public function collection()
    {
        $query = Product::query();

        if ($this->exportType === 'category') {
            if (!empty($this->subcategoryId)) {
                $query->whereIn('category_id', $this->subcategoryId);
            } else {
                $query->whereIn('category_id', $this->categoryId);
            }
        } elseif ($this->exportType === 'brand') {
            $query->whereIn('brand_id', $this->brandId);
        } elseif ($this->exportType === 'all_without_outofstock') {
            $query->where('current_stock', '>', 0);
        }

        if ($this->excludeOutOfStock) {
            $query->where('current_stock', '>', 0);
        }

        return $query->get();
    }

    public function headings(): array
    {
        $active_langs = Language::where('status', 1)->pluck('code')->toArray();
        $active_langs = array_filter($active_langs, function ($lang) {
            return $lang !== 'en';
        });
        $active_langs = array_values($active_langs);

        $fields = [
            'id',
            'name',
            'description',
            'category_id',
            'brand_id',
            'video_provider',
            'video_link',
            'tags',
            'unit_price',
            'purchase_price',
            'unit',
            'slug',
            'current_stock',
            'sku',
            'meta_title',
            'meta_description',
            'thumbnail_img',
            'photos',
            'langs',
            'product_url'
        ];

        if ($this->categoryId) {
            $fields[] = 'category_name';
        }

        if ($this->subcategoryId) {
            $fields[] = 'subcategory_name';
        }

        if ($this->brandId) {
            $fields[] = 'brand_name';
        }

        foreach ($active_langs as $lang) {
            $fields[] = 'product_url_' . $lang;
            $fields[] = 'name_' . $lang;
            $fields[] = 'description_' . $lang;
            $fields[] = 'meta_title_' . $lang;
            $fields[] = 'meta_description_' . $lang;
            $fields[] = 'unit_' . $lang;
        }

        return $fields;
    }

    public function map($product): array
    {
        $active_langs = Language::where('status', 1)->pluck('code')->toArray();
        $active_langs = array_filter($active_langs, function ($lang) {
            return $lang !== 'en';
        });
        $active_langs = array_values($active_langs);

        $baseUrl = env('APP_URL', 'https://localhost/demo');

        $stockSkus = is_countable($product->stocks) ? implode(', ', $product->stocks->pluck('sku')->toArray()) : '';
        $langs = is_countable($product->product_translations) ? implode(', ', $product->product_translations->pluck('lang')->toArray()) : '';

        $photoIds = is_array($product->photos) ? $product->photos : explode(',', $product->photos);
        $photoUrls = \App\Models\Upload::whereIn('id', $photoIds)->get()->map(function ($upload) use ($baseUrl) {
            return !empty($upload->file_name) ? "{$baseUrl}/public/{$upload->file_name}" : $upload->external_link;
        })->toArray();

        $thumbnailIds = is_array($product->thumbnail_img) ? $product->thumbnail_img : explode(',', $product->thumbnail_img);
        $thumbnailUrls = \App\Models\Upload::whereIn('id', $thumbnailIds)->get()->map(function ($upload) use ($baseUrl) {
            return !empty($upload->file_name) ? "{$baseUrl}/public/{$upload->file_name}" : $upload->external_link;
        })->toArray();

        $defaultProductUrl = "{$baseUrl}/product/{$product->slug}";

        $mappedData = [
            $product->id,
            $product->getTranslation('name', 'en'),
            strip_tags($product->getTranslation('description', 'en')),
            $product->category_id,
            $product->brand_id,
            $product->video_provider,
            $product->video_link,
            $product->tags,
            (int) $product->unit_price,
            (int) $product->purchase_price,
            $product->unit,
            $product->slug,
            $product->current_stock,
            $stockSkus,
            $product->getTranslation('meta_title', 'en', false),
            $product->getTranslation('meta_description', 'en', false),
            implode(', ', $thumbnailUrls),
            implode(', ', $photoUrls),
            $langs,
            $defaultProductUrl
        ];

        if ($this->categoryId) {
            $mappedData[] = optional($product->category)->name;
        }

        if ($this->subcategoryId) {
            $mappedData[] = optional($product->subcategory)->name;
        }

        if ($this->brandId) {
            $mappedData[] = optional($product->brand)->name;
        }

        // Add dynamic language translations to the array (excluding 'en')
        foreach ($active_langs as $lang) {
            $productUrl = "{$baseUrl}/{$lang}/product/{$product->slug}";
            $mappedData[] = $productUrl;
            $mappedData[] = (string) $product->getTranslation('name', $lang, true);
            $mappedData[] = (string) strip_tags($product->getTranslation('description', $lang, true));
            $mappedData[] = (string) $product->getTranslation('meta_title', $lang, true);
            $mappedData[] = (string) $product->getTranslation('meta_description', $lang, true);
            $mappedData[] = (string) $product->getTranslation('unit', $lang, true);
        }

        return $mappedData;
    }

        /**
     * Apply styles to the Excel sheet.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Apply bold styling to the first row (headings)
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
